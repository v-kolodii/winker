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

readonly class TaskHasFilesProvider implements ProviderInterface
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

        $taskId = (int) $uriVariables['taskId'] ?? null;

        if (empty($taskId)) {
            throw new \InvalidArgumentException();
        }

        $newManager = $this->getNewManager($user);

        $task = $newManager->getRepository(Task::class)->find($taskId);

        $files = $newManager->getRepository(TaskHasFile::class)->findBy(['task' => $task]);

        return $this->mapToDTOs($files);
    }

    /**
     * @param TaskHasFile[] $files
     */
    private function mapToDTOs(array $files): array
    {
        return array_map(static function (TaskHasFile $file) {
            return TasksFileDTO::fromEntity($file);
        }, $files);
    }
}
