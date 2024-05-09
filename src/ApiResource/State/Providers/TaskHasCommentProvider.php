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
        $taskId = (int) $uriVariables['taskId'] ?? null;
        $commentId = (int) $uriVariables['commentId'] ?? null;

        if (empty($taskId) || empty($commentId)) {
            throw new \InvalidArgumentException();
        }

        $newManager = $this->getNewManager($user);

        $task = $newManager->getRepository(Task::class)->find($taskId);
        $comment = $newManager->getRepository(TaskHasComment::class)->findOneBy(['id' => $commentId, 'task' => $task]);

        return CommentDTO::fromEntity($comment);
    }
}
