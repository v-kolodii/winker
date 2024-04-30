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
use App\Doctrine\CompanyEntityManager;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class TaskItemProvider implements ProviderInterface
{
    use LinksHandlerTrait;

    /**
     * @param QueryItemExtensionInterface[] $itemExtensions
     */
    public function __construct(
        ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
        private readonly ManagerRegistry $managerRegistry,
        private readonly Security $security,
        private readonly CompanyEntityManager   $companyEntityManagerService,
        private readonly iterable $itemExtensions = [],
        ContainerInterface $handleLinksLocator = null
    ) {
        $this->resourceMetadataCollectionFactory = $resourceMetadataCollectionFactory;
        $this->handleLinksLocator = $handleLinksLocator;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?object
    {
        /**@var UserInterface $user */
        $user = $this->security->getUser();
        if (! $user->getCompany()) {
            throw new RuntimeException(sprintf('%s. User ID: %d must have a company', __CLASS__, $user->getId()));
        }

        $entityClass = $operation->getClass();
        if (($options = $operation->getStateOptions()) && $options instanceof Options && $options->getEntityClass()) {
            $entityClass = $options->getEntityClass();
        }

        /** @var EntityManagerInterface $manager */
        $manager = $this->managerRegistry->getManagerForClass(User::class);

        $connection = $manager->getConnection();
        $connection->changeDatabase($user->getCompany()->getDbUrl());

        /** @var EntityManagerInterface $newManager */
        $newManager = $this->companyEntityManagerService->getEntityManager();

        $fetchData = $context['fetch_data'] ?? true;
        if (!$fetchData && \array_key_exists('id', $uriVariables)) {
            // todo : if uriVariables don't contain the id, this fails. This should behave like it does in the following code
            return $newManager->getReference($entityClass, $uriVariables);
        }

        $repository = $newManager->getRepository($entityClass);
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
