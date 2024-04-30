<?php

namespace App\DTO;

use App\Entity\TaskHasComment;
use Symfony\Component\Serializer\Annotation\Groups;

class CommentDTO
{
    public function __construct(
        #[Groups(['comment:read', 'comment:collection:read'])]
        public ?int $id = null,
        #[Groups(['comment:read', 'comment:collection:read'])]
        public ?int $task = null,
        #[Groups(['comment:write', 'comment:read', 'comment:collection:read'])]
        public ?string $comment = null,
        #[Groups(['comment:read', 'comment:collection:read'])]
        public ?int $user = null,
        #[Groups(['comment:read', 'comment:collection:read'])]
        public ?string $createdAt = null,
        #[Groups(['comment:read', 'comment:collection:read'])]
        public ?string $updatedAt = null,
    ){}

    public static function fromEntity(TaskHasComment $taskHasComment): self
    {
        return new self(
            id: $taskHasComment->getId(),
            task: $taskHasComment->getTask()?->getId(),
            comment: $taskHasComment->getComment(),
            user: $taskHasComment->getUserId(),
            createdAt: $taskHasComment->getCreatedAt()->format(\DateTime::ATOM),
            updatedAt: $taskHasComment->getUpdatedAt()->format(\DateTime::ATOM),
        );
    }
}
