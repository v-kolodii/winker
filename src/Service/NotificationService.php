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
    public function sendNotification(string $type, Task $task): void
    {
        $message = match ($type){
            self::NEW => $this->createNewMessage($task),
            self::UPDATED => $this->createUpdatedMessage($task),
        };

        $this->messaging->send($message);
    }

    /**
     * @throws EntityNotFoundException
     */
    private function createNewMessage(Task $task): CloudMessage
    {
        $performerId = $task->getPerformerId();
        $userDevice = $this->entityManager->getRepository(UserDevice::class)->findOneBy(['userId' => $performerId]);

        if (!$userDevice->getDeviceToken()) {
            throw new EntityNotFoundException('User\'s device token not found. User id ' . $performerId);
        }
        $title = 'Нове завдання!';
        $body = sprintf(
            'Створене нове завдання "%s"',
            $task->getTitle()
        );

        $notification = Notification::fromArray([
            'title' => $title,
            'body' => $body,
        ]);

        return CloudMessage::withTarget('token', $userDevice->getDeviceToken())
            ->withNotification($notification)
            ->withData($task->toArray());
    }

    private function createUpdatedMessage(Task $task): CloudMessage
    {
        $recipientId = $task->getPerformerId();

        $userDevice = $this->entityManager->getRepository(UserDevice::class)->findOneBy(['userId' => $recipientId]);
        $deviceToken = $userDevice->getDeviceToken();

        if (!$deviceToken) {
            throw new EntityNotFoundException('User\'s device token not found. User id ' . $recipientId);
        }

        $title = 'Завдання оновлено!';
        $body = sprintf(
            'Завдання %s оновлено!',
            $task->getTitle()
        );

        $notification = Notification::fromArray([
            'title' => $title,
            'body' => $body,
        ]);

        return CloudMessage::withTarget('token', $deviceToken)
            ->withNotification($notification)
            ->withData($task->toArray());
    }
}
