<?php

namespace App\DTO;

use App\Entity\Task;
use Symfony\Component\Serializer\Annotation\Groups;

class TaskDTO
{
    public function __construct(
        #[Groups(['task:update:read'])]
        public ?int $id = null,
        #[Groups(['task:update', 'task:update:read'])]
        public ?string $title = null,
        #[Groups(['task:update', 'task:update:read'])]
        public ?string $description = null,
        #[Groups(['task:update', 'task:update:read'])]
        public ?int $taskType = null,
        #[Groups(['task:update', 'task:update:read'])]
        public ?string $typeBasePlaneDate = null,
        #[Groups(['task:update', 'task:update:read'])]
        public ?string $typeRegDailyFinishedTime = null,
        #[Groups(['task:update', 'task:update:read'])]
        public ?string $typeRegWeeklyDay = null,
        #[Groups(['task:update', 'task:update:read'])]
        public ?string $typeRegWeeklyTime = null,
        #[Groups(['task:update', 'task:update:read'])]
        public ?int $typeRegMonthDay = null,
        #[Groups(['task:update', 'task:update:read'])]
        public ?string $typeRegMonthTime = null,
        #[Groups(['task:update', 'task:update:read'])]
        public ?string $finishedDate = null,
        #[Groups(['task:update', 'task:update:read'])]
        public ?int $winkType = null,
        #[Groups(['task:update', 'task:update:read'])]
        public ?int $status = null,
        #[Groups(['task:update', 'task:update:read'])]
        public ?int $performerId = null,
        #[Groups(['task:update:read'])]
        public ?int $userId = null,
        #[Groups(['task:update', 'task:update:read'])]
        public ?int $parent = null,
        #[Groups(['task:update', 'task:update:read'])]
        public ?bool $listEnable = null,
        #[Groups(['task:update:read'])]
        public ?string $createdAt = null,
    ){}

    public static function fromEntity(Task $task): self
    {
        return new self(
            id: $task->getId(),
            title: $task->getTitle(),
            description: $task->getDescription(),
            taskType: $task->getTaskType()->value,
            typeBasePlaneDate: $task->getTypeBasePlaneDate()?->format(\DateTime::ATOM),
            typeRegWeeklyDay: $task->getTypeRegWeeklyDay(),
            typeRegWeeklyTime: $task->getTypeRegWeeklyTime()?->format(\DateTime::ATOM),
            typeRegMonthDay: $task->getTypeRegMonthDay(),
            typeRegMonthTime: $task->getTypeRegMonthTime()?->format(\DateTime::ATOM),
            finishedDate: $task->getFinishedDate()?->format(\DateTime::ATOM),
            winkType: $task->getWinkType()->value,
            status: $task->getStatus()->value,
            performerId: $task->getPerformerId(),
            userId: $task->getUserId(),
            parent: $task->getParent()?->getId(),
            listEnable: $task->getListEnable(),
            createdAt: $task->getCreatedAt()?->format(\DateTime::ATOM),
        );
    }
}
