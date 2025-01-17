<?php

namespace App\Service;

use App\Entity\Task;
use App\Message\UserTaskNotification;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;

class AsyncNotificationService
{
    public const string NEW = 'new';
    public const string UPDATED = 'updated';

    private AMQPStreamConnection $amqpConnection;
    private AMQPChannel $amqpChannel;

    public function __construct(
        private MessageBusInterface $messageBus,
        private readonly LoggerInterface $logger,
        private readonly ParameterBagInterface $parameterBag,
    ) {
        $username = $this->parameterBag->get('messenger_user');
        $password = $this->parameterBag->get('messenger_pass');
        $this->amqpConnection = new AMQPStreamConnection('rabbitmq', 5672, $username, $password, '/');
        $this->amqpChannel = $this->amqpConnection->channel();
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

        $notification = $this->dispatchNotification($object, $task, $performerId);

        $this->logger->info('NEW ASYNC MESSAGE SEND', [
            'type' => $object->getMessageType(),
            'message' => sprintf('%s /-/ %s /-/ %s',
                $notification->target,
                $notification->task,
                $notification->mesType
            )
        ]);
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

        $notification = $this->dispatchNotification($object, $task, $recipientId);

        $this->logger->info('UPDATE ASYNC MESSAGE SEND', [
            'type' => $object->getMessageType(),
            'message' => sprintf('%s /-/ %s /-/ %s',
                $notification->target,
                $notification->task,
                $notification->mesType
            )
        ]);
    }

    public function __destruct()
    {
        if (isset($this->amqpChannel) && $this->amqpChannel->is_open) {
            $this->amqpChannel->close();
        }

        if (isset($this->amqpConnection)) {
            $this->amqpConnection->close();
        }
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
        $notification = new UserTaskNotification(
            target: json_encode($object->toArray()),
            task: json_encode($task->toArray()),
            mesType: $object->getMessageType(),
        );

        $this->amqpChannel->exchange_declare(
            'user_notifications_exchange',
            'direct',
            false,
            true,
            false
        );
        $queueName = sprintf('user_queue_%s', $recipientId);
        $routingKey = $queueName;

        $this->amqpChannel->queue_declare($queueName, false, true, false, false);
        $this->amqpChannel->queue_bind($queueName, 'user_notifications_exchange', $routingKey);

        $this->messageBus->dispatch($notification, [
            new AmqpStamp($routingKey),
        ]);

        return $notification;
    }
}
