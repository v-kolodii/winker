<?php

namespace App\Service;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Symfony\Bundle\SecurityBundle\Security;

class NotificationService
{
    private const string NEW = 'new';
    private const string UPDATED = 'updated';

    private \Kreait\Firebase\Contract\Messaging $messaging;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private readonly Security $security,
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
        $user = $this->entityManager->getRepository(User::class)->find($performerId);

        if (!$user) {
           throw new EntityNotFoundException('User not found with id ' . $performerId);
        }

        $deviceToken = $user->getDeviceId();
        if (!$deviceToken) {
            throw new EntityNotFoundException('User\'s device token not found. User id ' . $performerId);
        }

        $creator = $this->security->getUser();

        $title = 'Нове завдання!';
        $body = sprintf(
            'Користувач %s створив нове завдання "%s"',
            $creator,
            $task->getTitle()
        );

        $notification = Notification::fromArray([
            'title' => $title,
            'body' => $body,
        ]);

        return CloudMessage::withTarget('token', $deviceToken)
            ->withNotification($notification);
    }

    private function createUpdatedMessage(Task $task): CloudMessage
    {
        $initiator = $this->security->getUser();
        $recipient = $this->entityManager->getRepository(User::class)->find($task->getPerformerId());

        if ($initiator->getId() !== $task->getUserId()) {
            [$initiator, $recipient] = [$recipient, $initiator];
        }

        $deviceToken = $recipient->getDeviceId();

        if (!$deviceToken) {
            throw new EntityNotFoundException('User\'s device token not found. User id ' . $recipient->getId());
        }

        $title = 'Завдання оновлено!';
        $body = sprintf(
            'Користувач %s оновив завдання %s',
            $initiator,
            $task->getTitle()
        );

        $notification = Notification::fromArray([
            'title' => $title,
            'body' => $body,
        ]);

        return CloudMessage::withTarget('token', $deviceToken)
            ->withNotification($notification);
    }
}
