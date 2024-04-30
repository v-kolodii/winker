<?php

namespace App\ApiResource\State\Providers;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Doctrine\CompanyEntityManager;
use App\DTO\CommentDTO;
use App\Entity\Task;
use App\Entity\TaskHasComment;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

readonly class TaskHasCommentProvider implements ProviderInterface
{
    use UserTrait;

    public function __construct(
        private ManagerRegistry      $managerRegistry,
        private Security             $security,
        private CompanyEntityManager $companyEntityManagerService,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): CommentDTO
    {
        $user = $this->getUser();
        $taskId = (int) $uriVariables['taskId'];
        $commentId = (int) $uriVariables['commentId'];

        if (empty($taskId) || empty($commentId)) {
            throw new \InvalidArgumentException();
        }

        /** @var EntityManagerInterface $manager */
        $manager = $this->managerRegistry->getManagerForClass(User::class);
        $connection = $manager->getConnection();
        $connection->changeDatabase($user->getCompany()->getDbUrl());
        /** @var EntityManagerInterface $newManager */
        $newManager = $this->companyEntityManagerService->getEntityManager();

        $task = $newManager->getRepository(Task::class)->find($taskId);
        $comment = $newManager->getRepository(TaskHasComment::class)->findOneBy(['id' => $commentId, 'task' => $task]);

        return CommentDTO::fromEntity($comment);
    }
}
