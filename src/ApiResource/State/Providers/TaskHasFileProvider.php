<?php

namespace App\ApiResource\State\Providers;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Doctrine\CompanyEntityManager;
use App\DTO\TasksFileDTO;
use App\Entity\Task;
use App\Entity\TaskHasFile;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

readonly class TaskHasFileProvider implements ProviderInterface
{
    use UserTrait;

    public function __construct(
        private ManagerRegistry      $managerRegistry,
        private Security             $security,
        private CompanyEntityManager $companyEntityManagerService,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): TasksFileDTO
    {
        $user = $this->getUser();
        $taskId = (int) $uriVariables['taskId'] ?? null;
        $fileId = (int) $uriVariables['fileId'] ?? null;

        if (empty($taskId) || empty($fileId)) {
            throw new \InvalidArgumentException();
        }

        $newManager = $this->getNewManager($user);

        $task = $newManager->getRepository(Task::class)->find($taskId);
        $file = $newManager->getRepository(TaskHasFile::class)->findOneBy(['id' => $fileId, 'task' => $task]);

        return TasksFileDTO::fromEntity($file);
    }
}
