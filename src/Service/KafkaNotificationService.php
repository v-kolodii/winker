<?php

namespace App\Service;

use App\Entity\Task;
use App\Message\UserTaskNotification;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\TransportInterface;

class KafkaNotificationService
{
    public const string NEW = 'new';
    public const string UPDATED = 'updated';


    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly TransportInterface $transport,
    ) {
    }

    public function sendNotification(string $type, object $object): void
    {
        match ($type) {
            self::NEW => $this->createNewMessage($object),
            self::UPDATED => $this->createUpdatedMessage($object),
        };
    }

    private function createNewMessage(object $object): void
    {
        if ($object instanceof Task) {
            $task = $object;
            $performerId = $task->getPerformerId();
        } else {
            $task = $object->getTask();
            $performerId = $task->getPerformerId();
            if ($object->getUserId() == $performerId) {
                $performerId = $task->getUserId();
            }
        }
        $this->logger->info('NEW ASYNC MESSAGE SEND', [
            'task' => $task->toArray()
        ]);
        $this->dispatchNotification($object, $task, $performerId);
    }

    private function createUpdatedMessage(object $object)
    {
        if ($object instanceof Task) {
            $task = $object;
            $recipientId = $task->getPerformerId();
        } else {
            $task = $object->getTask();
            $recipientId = $task->getPerformerId();
            if ($object->getUserId() == $recipientId) {
                $recipientId = $task->getUserId();
            }
        }
        $this->logger->info('UPDATE ASYNC MESSAGE SEND', [
            'task' => $task->toArray()
        ]);
        $this->dispatchNotification($object, $task, $recipientId);
    }


    /**
     *
     * @param mixed $object
     * @param Task $task
     * @param int|null $recipientId
     * @return UserTaskNotification
     */
    public function dispatchNotification(mixed $object, Task $task, ?int $recipientId): UserTaskNotification
    {
        $topic = "user_notifications_{$recipientId}";
        $notification = new UserTaskNotification(
            target: $object->toArray(),
            task: $task->toArray(),
            mesType: $object->getMessageType(),
            topic: $topic
        );

        $this->transport->send(new Envelope($notification));

        return $notification;
    }
}
