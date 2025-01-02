<?php

namespace App\Service;

use App\Entity\Task;
use App\Entity\UserDevice;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class MercureNotificationService
{
    public const string NEW = 'new';
    public const string UPDATED = 'updated';

    public function __construct(
        private readonly HubInterface $hub,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    )
    {
    }

    /**
     * @throws EntityNotFoundException
     */
    public function sendNotification(string $type, object $object): void
    {
        $message = match ($type) {
            self::NEW => $this->createNewMessage($object),
            self::UPDATED => $this->createUpdatedMessage($object),
        };

        $this->hub->publish($message);
        $this->logger->info('MERCURE MESSAGE SENDED', ['type' => $type, 'result' => $message->getData()]);
    }

    private function createNewMessage(object $object): Update
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

        $userDevice = $this->entityManager->getRepository(UserDevice::class)->findOneBy(['userId' => $performerId]);
        $deviceToken = $userDevice?->getDeviceToken();
        if (!$deviceToken) {
            throw new EntityNotFoundException('User\'s device token not found. User id ' . $performerId);
        }

        $data = [
            'target' => json_encode($object->toArray()),
            'task' => json_encode($task->toArray()),
            'mes_type' => $object->getMessageType()
        ];

        return new Update(
            "/user/{$deviceToken}/tasks",
            json_encode($data)
        );
    }

    private function createUpdatedMessage(object $object): Update
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

        $userDevice = $this->entityManager->getRepository(UserDevice::class)->findOneBy(['userId' => $recipientId]);
        $deviceToken = $userDevice->getDeviceToken();

        if (!$deviceToken) {
            throw new EntityNotFoundException('User\'s device token not found. User id ' . $recipientId);
        }

        $data = [
            'target' => json_encode($object->toArray()),
            'task' => json_encode($task->toArray()),
            'mes_type' => $object->getMessageType()
        ];

        return new Update(
            "/user/{$deviceToken}/tasks",
            json_encode($data)
        );
    }
}
