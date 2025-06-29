<?php

namespace App\EventListener;

use App\Entity\Task;
use App\Service\KafkaNotificationService;
use App\Service\NotificationService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Psr\Log\LoggerInterface;
use Throwable;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Task::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Task::class)]
readonly class TaskChangedListener
{
    public function __construct(
        private KafkaNotificationService $asyncNotificationService,
        private NotificationService $notificationService,
        private LoggerInterface $logger,
    ) {
    }

    public function postPersist(Task $task, LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Task) {
            return;
        }

        try {
            $this->asyncNotificationService->sendNotification('new', $entity);
            $this->notificationService->sendNotification('new', $entity);

        } catch (\Exception|Throwable $exception) {
            $this->logger->error( '[NEW TASK. SEND NOTIFICATION ERROR]: ' . $exception->getMessage());
        }
    }

    public function postUpdate(Task $task, PostUpdateEventArgs $event): void
    {
        try {
            $this->asyncNotificationService->sendNotification('updated', $task);
            $this->notificationService->sendNotification('updated', $task);
        } catch (\Exception|Throwable $exception) {
            $this->logger->error( '[UPDATE TASK. SEND NOTIFICATION ERROR]: ' . $exception->getMessage());
        }
    }
}
