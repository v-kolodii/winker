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
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

class CommentUpdateProcessor implements ProcessorInterface
{
    use UserTrait;

    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly Security $security,
        private readonly CompanyEntityManager   $companyEntityManagerService,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): CommentDTO
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
        $updatedAt = $data->updatedAt !== null ? new \DateTime($data->updatedAt) : new \DateTime();
        $comment->setComment($data->comment)->setUpdatedAt($updatedAt);

        $newManager->flush();

        return CommentDTO::fromEntity($comment);
    }
}
