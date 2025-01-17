<?php

namespace App\Message;

class UserTaskNotification implements NotificationInterface
{
    public function __construct(
        public string $target,
        public string $task,
        public string $mesType,
    ) {}
}
