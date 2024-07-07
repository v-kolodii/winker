<?php

namespace App\ApiResource\State\Processors;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\State\Providers\UserTrait;
use App\Doctrine\CompanyEntityManager;
use App\DTO\CommentDTO;
use App\DTO\TasksFileDTO;
use App\Entity\Task;
use App\Entity\TaskHasComment;
use App\Entity\TaskHasFile;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

class TaskHasFileCreateProcessor implements ProcessorInterface
{
    use UserTrait;

    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly Security $security,
        private readonly CompanyEntityManager   $companyEntityManagerService,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TasksFileDTO
    {
        $user = $this->getUser();
        $taskId = (int) $uriVariables['taskId'] ?? null;
        if (empty($taskId)) {
            throw new \InvalidArgumentException();
        }

        $newManager = $this->getNewManager($user);
        $createdAt = $data->createdAt !== null ? new \DateTime($data->createdAt) : new \DateTime();
        $file = (new TaskHasFile())
            ->setLocalName($data->local_name)
            ->setGlobalName($data->global_name)
            ->setCreatedAt($createdAt)
            ->setUserId($user->getId());

        $task = $newManager->getRepository(Task::class)->find($taskId);

        if ($newManager->getUnitOfWork()->getEntityState($task) !== UnitOfWork::STATE_MANAGED) {
            $task = $newManager->merge($task);
            $file->setTask($task);
        }
        $newManager->persist($file);
        $newManager->flush();

        return TasksFileDTO::fromEntity($file);
    }
}
