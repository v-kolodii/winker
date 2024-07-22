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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class TaskHasCommentByIdProvider implements ProviderInterface
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
        $commentId = (int) $uriVariables['id'] ?? null;

        if (!$commentId) {
            throw new \InvalidArgumentException();
        }

        $newManager = $this->getNewManager($user);

        $comment = $newManager->getRepository(TaskHasComment::class)->find($commentId);

        if ($comment === null) {
            throw new NotFoundHttpException('Comment not found');
        }

        return CommentDTO::fromEntity($comment);
    }
}
