<?php

namespace App\Service;

use App\Entity\Task;
use App\Message\UserTaskNotification;
use Psr\Log\LoggerInterface;
use RdKafka\Conf;
use RdKafka\Exception;
use RdKafka\Producer;

class KafkaNotificationService
{
    public const string NEW = 'new';
    public const string UPDATED = 'updated';
    private const int DEFAULT_TIMEOUT = 1000;

    private Producer $producer;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly string $kafkaBroker = 'kafka:9092',
    ) {
        $conf = new Conf();
        $conf->set('enable.idempotence', 'true');
        $this->producer = new Producer($conf);
        $this->producer->addBrokers($this->kafkaBroker);
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
     * @throws Exception
     */
    public function dispatchNotification(mixed $object, Task $task, ?int $recipientId): UserTaskNotification
    {
//        $topic = new RdKafkaTopic("user_notifications_{$recipientId}");
//        $topic = $this->kafkaContext->createTopic("user_notifications_{$recipientId}");
        $notification = new UserTaskNotification(
            target: $object->toArray(),
            task: $task->toArray(),
            mesType: $object->getMessageType(),
            topic: "user_notifications_{$recipientId}"
        );

//        $message = new RdKafkaMessage(json_encode([
//
//        ]));

//        $producer = $this->kafkaContext->createProducer();

//        $kafkaMessage = $this->kafkaContext->createMessage(json_encode([
//            'target'=> $object->toArray(),
//            'task'=> $task->toArray(),
//            'mesType'=> $object->getMessageType(),
//            'topic'=> $topic->getTopicName()
//        ]));

//        $producer->send($topic, $kafkaMessage);

//        $this->producer->sendEvent(
//            "user_notification_{$recipientId}",
//            json_encode([
//                'target'=> $object->toArray(),
//                'task'=> $task->toArray(),
//                'mesType'=> $object->getMessageType(),
//                'topic'=> "user_notification_{$recipientId}"
//            ])
//        );

        $messageBody = json_encode([
            'target'=> $object->toArray(),
            'task'=> $task->toArray(),
            'mesType'=> $object->getMessageType()
        ]);

//        $this->logger->info('Attempting to send Kafka message', [
//            'topic' => "user_notification_2",
//            'message' => $messageBody
//        ]);

        $topic = $this->producer->newTopic("user_notifications_{$recipientId}");
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, $messageBody);

        for ($i = 0; $i < 3; $i++) {
            $this->logger->info('KAFKA ATTEMP: -' . $i);
            $result = $this->producer->flush(self::DEFAULT_TIMEOUT);
            if (RD_KAFKA_RESP_ERR_NO_ERROR === $result) {
                $this->logger->info('KAFKA ATTEMP SUCCESS: -' . $i);
                $this->logger->info('Kafka message sent successfully', [$messageBody]);

                return $notification;
            }
        }

        $this->logger->critical('Kafka message not sent', [$messageBody]);

        return $notification;
    }
}
