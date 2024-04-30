<?php

namespace App\ApiResource\State\Processors;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\State\Providers\UserTrait;
use App\Doctrine\CompanyEntityManager;
use App\DTO\CommentDTO;
use App\Entity\Task;
use App\Entity\TaskHasComment;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

class CommentCreateProcessor implements ProcessorInterface
{
    use UserTrait;

    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly Security $security,
        private readonly CompanyEntityManager   $companyEntityManagerService,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): CommentDTO
    {
        $user = $this->getUser();
        $taskId = (int) $uriVariables['taskId'];
        if (empty($taskId)) {
            throw new \InvalidArgumentException();
        }

        /** @var EntityManagerInterface $manager */
        $manager = $this->managerRegistry->getManagerForClass(User::class);
        $connection = $manager->getConnection();
        $connection->changeDatabase($user->getCompany()->getDbUrl());
        /** @var EntityManagerInterface $newManager */
        $newManager = $this->companyEntityManagerService->getEntityManager();
        $comment = (new TaskHasComment())
            ->setComment($data->comment)
            ->setUserId($user->getId());

        $task = $newManager->getRepository(Task::class)->find($taskId);

        if ($newManager->getUnitOfWork()->getEntityState($task) !== UnitOfWork::STATE_MANAGED) {
            $task = $newManager->merge($task);
            $comment->setTask($task);
        }
        $newManager->persist($comment);
        $newManager->flush();

        return CommentDTO::fromEntity($comment);
    }
}
