<?php

namespace App\EventListener;

use App\Entity\Task;
use App\Service\MercureNotificationService;
use App\Service\NotificationService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityNotFoundException;
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
        private LoggerInterface     $logger,
        private NotificationService $notificationService,
        private MercureNotificationService $mercureNotificationService,
    ) {
    }


    /**
     * @throws EntityNotFoundException
     */
    public function postPersist(Task $task, LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Task) {
            return;
        }
        $this->mercureNotificationService->sendNotification('new', $entity);
        try {
            $this->notificationService->sendNotification('new', $entity);

        } catch (\Exception|Throwable $exception) {
            $this->logger->error( '[NEW TASK. SEND NOTIFICATION ERROR]: ' . $exception->getMessage());
        }
    }

    /**
     * @throws EntityNotFoundException
     */
    public function postUpdate(Task $task, PostUpdateEventArgs $event): void
    {
        $this->mercureNotificationService->sendNotification('updated', $task);
        try {
            $this->notificationService->sendNotification('updated', $task);
        } catch (\Exception|Throwable $exception) {
            $this->logger->error( '[UPDATE TASK. SEND NOTIFICATION ERROR]: ' . $exception->getMessage());
        }
    }
}
