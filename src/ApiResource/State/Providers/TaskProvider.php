<?php

namespace App\ApiResource\State\Providers;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Doctrine\CompanyEntityManager;
use App\DTO\TasksFileDTO;
use App\Entity\Notification;
use App\Entity\Task;
use App\Entity\TaskHasFile;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Utils;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class TaskProvider implements ProviderInterface
{
    use UserTrait;

    public function __construct(
        private ManagerRegistry      $managerRegistry,
        private Security             $security,
        private CompanyEntityManager $companyEntityManagerService,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Task
    {
        $user = $this->getUser();
        $id = (int) $uriVariables['id'] ?? null;

        if (empty($id)) {
            throw new \InvalidArgumentException();
        }

        $newManager = $this->getNewManager($user);

        $task = $newManager->getRepository(Task::class)->find($id);

        if ($task === null) {
            throw new NotFoundHttpException('Task not found');
        }

        return $task;
    }
}
