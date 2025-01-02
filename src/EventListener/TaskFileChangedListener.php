<?php

namespace App\EventListener;

use App\Entity\TaskHasFile;
use App\Service\MercureNotificationService;
use App\Service\NotificationService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Psr\Log\LoggerInterface;
use Throwable;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: TaskHasFile::class)]
readonly class TaskFileChangedListener
{
    public function __construct(
        private LoggerInterface     $logger,
        private NotificationService $notificationService,
        private MercureNotificationService $mercureNotificationService,
    ) {
    }


    public function postPersist(TaskHasFile $taskHasFile, LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof TaskHasFile) {
            return;
        }

        try {
            $this->notificationService->sendNotification('new', $entity);
            $this->mercureNotificationService->sendNotification('new', $entity);
        } catch (\Exception|Throwable $exception) {
            $this->logger->error( '[NEW FILE. SEND NOTIFICATION ERROR]: ' . $exception->getMessage());
        }
    }
}
