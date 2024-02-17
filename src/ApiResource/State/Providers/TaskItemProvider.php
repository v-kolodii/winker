<?php

namespace App\ApiResource\State\Providers;

use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryResultItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Exception\RuntimeException;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\State\LinksHandlerTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Container\ContainerInterface;

class TaskItemProvider implements ProviderInterface
{
    use LinksHandlerTrait;

    /**
     * @param QueryItemExtensionInterface[] $itemExtensions
     */
    public function __construct(ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory, private readonly ManagerRegistry $managerRegistry, private readonly iterable $itemExtensions = [], ContainerInterface $handleLinksLocator = null)
    {
        $this->resourceMetadataCollectionFactory = $resourceMetadataCollectionFactory;
        $this->handleLinksLocator = $handleLinksLocator;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?object
    {
        $entityClass = $operation->getClass();
        if (($options = $operation->getStateOptions()) && $options instanceof Options && $options->getEntityClass()) {
            $entityClass = $options->getEntityClass();
        }


        //TODO CHANGE DB HERE

        /** @var EntityManagerInterface $manager */
        $manager = $this->managerRegistry->getManagerForClass($entityClass);

        $fetchData = $context['fetch_data'] ?? true;
        if (!$fetchData && \array_key_exists('id', $uriVariables)) {
            // todo : if uriVariables don't contain the id, this fails. This should behave like it does in the following code
            return $manager->getReference($entityClass, $uriVariables);
        }

        $repository = $manager->getRepository($entityClass);
        if (!method_exists($repository, 'createQueryBuilder')) {
            throw new RuntimeException('The repository class must have a "createQueryBuilder" method.');
        }

        $queryBuilder = $repository->createQueryBuilder('o');
        $queryNameGenerator = new QueryNameGenerator();

        if ($handleLinks = $this->getLinksHandler($operation)) {
            $handleLinks($queryBuilder, $uriVariables, $queryNameGenerator, ['entityClass' => $entityClass, 'operation' => $operation] + $context);
        } else {
            $this->handleLinks($queryBuilder, $uriVariables, $queryNameGenerator, $context, $entityClass, $operation);
        }

        foreach ($this->itemExtensions as $extension) {
            $extension->applyToItem($queryBuilder, $queryNameGenerator, $entityClass, $uriVariables, $operation, $context);

            if ($extension instanceof QueryResultItemExtensionInterface && $extension->supportsResult($entityClass, $operation, $context)) {
                return $extension->getResult($queryBuilder, $entityClass, $operation, $context);
            }
        }

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
