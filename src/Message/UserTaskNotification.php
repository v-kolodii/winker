<?php

namespace App\Message;

class UserTaskNotification implements NotificationInterface
{
    public function __construct(
        public array $target,
        public array $task,
        public string $mesType,
        public string $topic,
    ) {}

    public function getTopic(): string
    {
        return $this->topic;
    }

    public function getTarget(): array
    {
        return $this->target;
    }

    public function getTask(): array
    {
        return $this->task;
    }

    public function getMesType(): string
    {
        return $this->mesType;
    }
}
