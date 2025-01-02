<?php

namespace App\EventListener;

use App\Entity\TaskHasComment;
use App\Service\MercureNotificationService;
use App\Service\NotificationService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Psr\Log\LoggerInterface;
use Throwable;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: TaskHasComment::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: TaskHasComment::class)]
readonly class TaskCommentChangedListener
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
    public function postPersist(TaskHasComment $taskHasComment, LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof TaskHasComment) {
            return;
        }

        $this->mercureNotificationService->sendNotification('new', $entity);
        try {
            $this->notificationService->sendNotification('new', $entity);
        } catch (\Exception|Throwable $exception) {
            $this->logger->error( '[NEW COMMENT. SEND NOTIFICATION ERROR]: ' . $exception->getMessage());
        }
    }

    /**
     * @throws EntityNotFoundException
     */
    public function postUpdate(TaskHasComment $taskHasComment, PostUpdateEventArgs $event): void
    {
        $this->mercureNotificationService->sendNotification('updated', $taskHasComment);
        try {
            $this->notificationService->sendNotification('updated', $taskHasComment);
        } catch (\Exception|Throwable $exception) {
            $this->logger->error( '[UPDATE COMMENT. SEND NOTIFICATION ERROR]: ' . $exception->getMessage());
        }
    }
}
