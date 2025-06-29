<?php

namespace App\EventListener;

use App\Entity\TaskHasComment;
use App\Service\AsyncNotificationService;
use App\Service\KafkaNotificationService;
use App\Service\NotificationService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
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
        private KafkaNotificationService $asyncNotificationService,
        private NotificationService $notificationService,
        private LoggerInterface $logger,
    ) {
    }


    public function postPersist(TaskHasComment $taskHasComment, LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof TaskHasComment) {
            return;
        }

        try {
            $this->asyncNotificationService->sendNotification('new', $entity);
            $this->notificationService->sendNotification('new', $entity);
        } catch (\Exception|Throwable $exception) {
            $this->logger->error( '[NEW COMMENT. SEND NOTIFICATION ERROR]: ' . $exception->getMessage());
        }
    }

    public function postUpdate(TaskHasComment $taskHasComment, PostUpdateEventArgs $event): void
    {
        try {
            $this->asyncNotificationService->sendNotification('updated', $taskHasComment);
            $this->notificationService->sendNotification('updated', $taskHasComment);
        } catch (\Exception|Throwable $exception) {
            $this->logger->error( '[UPDATE COMMENT. SEND NOTIFICATION ERROR]: ' . $exception->getMessage());
        }
    }
}
