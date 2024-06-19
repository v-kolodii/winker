<?php

namespace App\EventListener;

use App\Entity\Task;
use App\Service\NotificationService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Task::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Task::class)]
readonly class TaskChangedListener
{
    public function __construct(private NotificationService $notificationService)
    {}

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Task) {
            return;
        }

        $this->notificationService->sendNotification('new', $entity);

    }

    public function postUpdate(Task $task, PostUpdateEventArgs $event): void
    {
        $this->notificationService->sendNotification('updated', $task);
    }

}
