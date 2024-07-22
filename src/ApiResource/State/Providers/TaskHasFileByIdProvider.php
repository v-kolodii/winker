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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class TaskHasFileByIdProvider implements ProviderInterface
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
        $fileId = (int) $uriVariables['id'] ?? null;

        if (!$fileId) {
            throw new \InvalidArgumentException();
        }

        $newManager = $this->getNewManager($user);

        $file = $newManager->getRepository(TaskHasFile::class)->find($fileId);

        if ($file === null) {
            throw new NotFoundHttpException('File not found');
        }

        return TasksFileDTO::fromEntity($file);
    }
}
