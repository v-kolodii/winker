<?php

namespace App\Service;

use App\Entity\Task;
use App\Entity\UserDevice;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class NotificationService
{
    private const string NEW = 'new';
    private const string UPDATED = 'updated';

    private Messaging $messaging;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        Factory $factory,
    ) {
        $this->messaging = $factory->createMessaging();
    }

    /**
     * @throws MessagingException
     * @throws FirebaseException|EntityNotFoundException
     */
    public function sendNotification(string $type, object $object): void
    {
        $message = match ($type) {
            self::NEW => $this->createNewMessage($object),
            self::UPDATED => $this->createUpdatedMessage($object),
        };

        $this->messaging->send($message);
    }

    /**
     * @throws EntityNotFoundException
     */
    private function createNewMessage(object $object): CloudMessage
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

        if (!$userDevice->getDeviceToken()) {
            throw new EntityNotFoundException('User\'s device token not found. User id ' . $performerId);
        }

        $notification = Notification::fromArray([
            'title' => $object->getNewNotificationTitle(),
        ]);

        $data = array_merge(
            ['target' => $object->toArray()],
            ['task' => $task->toArray()],
            ['message_type' => $object->getMessageType()]
        );

        return CloudMessage::withTarget('token', $userDevice->getDeviceToken())
            ->withNotification($notification)
            ->withData($data);
    }

    private function createUpdatedMessage(object $object): CloudMessage
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

        $notification = Notification::fromArray([
            'title' => $object->getUpdatedNotificationTitle(),
        ]);

        $data = array_merge(
            ['target' => $object->toArray()],
            ['task' => $task->toArray()],
            ['message_type' => $object->getMessageType()]
        );

        return CloudMessage::withTarget('token', $deviceToken)
            ->withNotification($notification)
            ->withData($data);
    }
}
