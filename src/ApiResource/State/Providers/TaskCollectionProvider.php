<?php

namespace App\ApiResource\State\Providers;


use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
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
use Symfony\Bundle\SecurityBundle\Security;

class TaskCollectionProvider implements ProviderInterface
{
    use LinksHandlerTrait;

    /**
     * @param QueryCollectionExtensionInterface[] $collectionExtensions
     */
    public function __construct(
        ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
        private readonly ManagerRegistry $managerRegistry,
        private readonly Security $security,
        private readonly iterable $collectionExtensions = [],
        ContainerInterface $handleLinksLocator = null,
    )
    {
        $this->resourceMetadataCollectionFactory = $resourceMetadataCollectionFactory;
        $this->handleLinksLocator = $handleLinksLocator;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|null|object
    {
        $entityClass = $operation->getClass();
        if (($options = $operation->getStateOptions()) && $options instanceof Options && $options->getEntityClass()) {
            $entityClass = $options->getEntityClass();
        }

        $user = $this->security->getUser();
        dd($user);



        //TODO CHANGE DB HERE


        /** @var EntityManagerInterface $manager */
        $manager = $this->managerRegistry->getManagerForClass($entityClass);

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

        foreach ($this->collectionExtensions as $extension) {
            $extension->applyToCollection($queryBuilder, $queryNameGenerator, $entityClass, $operation, $context);

            if ($extension instanceof QueryResultCollectionExtensionInterface && $extension->supportsResult($entityClass, $operation, $context)) {
                return $extension->getResult($queryBuilder, $entityClass, $operation, $context);
            }
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
