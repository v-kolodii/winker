<?php

declare(strict_types=1);

namespace App\ApiResource\State\Processors;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\State\Providers\UserTrait;
use App\Doctrine\CompanyEntityManager;
use App\Entity\Task;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

final readonly class RemoveTaskProcessor implements ProcessorInterface
{
    use UserTrait;

    public function __construct(
        private ManagerRegistry      $managerRegistry,
        private Security             $security,
        private CompanyEntityManager $companyEntityManagerService,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        $id = (int) $uriVariables['id'] ?? null;

        if (empty($id)) {
            throw new \InvalidArgumentException('ID cannot be empty.');
        }

        $user = $this->getUser();
        $newManager = $this->getNewManager($user);
        $task = $newManager->getRepository(Task::class)->find($uriVariables['id']);
        $task = $newManager->getReference(Task::class, $task->getId());
        $newManager->remove($task);
        $newManager->flush();
    }
}
