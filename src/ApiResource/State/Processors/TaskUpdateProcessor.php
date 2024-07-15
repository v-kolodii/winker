<?php

namespace App\ApiResource\State\Processors;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\State\Providers\UserTrait;
use App\Doctrine\CompanyEntityManager;
use App\DTO\TaskDTO;
use App\Entity\Enum\Status;
use App\Entity\Enum\TaskType;
use App\Entity\Enum\WinkType;
use App\Entity\Task;
use App\Service\NotificationService;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\ManagerRegistry;
use Kreait\Firebase\Exception\FirebaseException;
use Symfony\Bundle\SecurityBundle\Security;

class TaskUpdateProcessor implements ProcessorInterface
{
    use UserTrait;

    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly Security $security,
        private readonly CompanyEntityManager $companyEntityManagerService,
        private readonly NotificationService $notificationService,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TaskDTO
    {
        $user = $this->getUser();
        $taskId = (int) $uriVariables['id'] ?? null;

        if (empty($taskId)) {
            throw new \InvalidArgumentException();
        }

        $newManager = $this->getNewManager($user);

        /**@var Task $task*/
        $task = $newManager->getRepository(Task::class)->find($taskId);
        $task->setTitle($data->title ?? $task->getTitle());
        $task->setDescription($data->description ?? $task->getDescription());
        $task->setTaskType($data->taskType ? TaskType::from($data->taskType) : $task->getTaskType());
        $task->setTypeBasePlaneDate($data->typeBasePlaneDate !== null ? new \DateTime($data->typeBasePlaneDate) : $task->getTypeBasePlaneDate());
        $task->setTypeRegWeeklyDay($data->typeRegWeeklyDay ?? $task->getTypeRegWeeklyDay());
        $task->setTypeRegWeeklyTime($data->typeRegWeeklyTime !== null ? new \DateTime($data->typeRegWeeklyTime) : $task->getTypeRegWeeklyTime());
        $task->setTypeRegMonthDay($data->typeRegMonthlyDay ?? $task->getTypeRegMonthDay());
        $task->setTypeRegMonthTime($data->typeRegMonthTime !== null ? new \DateTime($data->typeRegMonthTime) : $task->getTypeRegMonthTime());
        $task->setFinishedDate($data->finishedDate !== null ? new \DateTime($data->finishedDate) : $task->getFinishedDate());
        $task->setWinkType($data->winkType ? WinkType::from($data->winkType) : $task->getWinkType());
        $task->setStatus($data->status ? Status::from($data->status) : $task->getStatus());
        $task->setPerformerId($data->performerId ?? $task->getPerformerId());
        $parent = $task->getParent();
        if ($data->parent !== null) {
            $parent = $newManager->getRepository(Task::class)->find($data->parent);
        }
        $task->setParent($parent);
        $task->setListEnable($data->listEnable ?? $task->getListEnable());

        $newManager->flush();

        try {
            $this->notificationService->sendNotification(NotificationService::UPDATED, $task);
        } catch (EntityNotFoundException|FirebaseException $e) {
        }

        return TaskDTO::fromEntity($task);
    }
}
