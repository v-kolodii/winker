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

readonly class TaskHasCommentsProvider implements ProviderInterface
{
    use UserTrait;

    public function __construct(
        private ManagerRegistry      $managerRegistry,
        private Security             $security,
        private CompanyEntityManager $companyEntityManagerService,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
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

        $task = $newManager->getRepository(Task::class)->find($taskId);

        $comments = $newManager->getRepository(TaskHasComment::class)->findBy(['task' => $task]);

        return $this->mapToDTOs($comments);
    }

    /**
     * @param TaskHasComment[] $comments
     */
    private function mapToDTOs(array $comments): array
    {
        return array_map(static function (TaskHasComment $comment) {
            return CommentDTO::fromEntity($comment);
        }, $comments);
    }
}
