<?php

namespace App\DTO;

use App\Entity\TaskHasFile;
use Symfony\Component\Serializer\Annotation\Groups;

class TasksFileDTO
{
    public function __construct(
        #[Groups(['file:read', 'file:collection:read'])]
        public ?int $id = null,
        #[Groups(['file:read', 'file:collection:read'])]
        public ?int $task = null,
        #[Groups(['file:write', 'file:read', 'file:collection:read'])]
        public ?string $local_name = null,
        #[Groups(['file:write', 'file:read', 'file:collection:read'])]
        public ?string $global_name = null,
        #[Groups(['file:read', 'file:collection:read'])]
        public ?int $user = null,
        #[Groups(['file:write', 'file:read', 'file:collection:read'])]
        public ?string $createdAt = null,
    ){}

    public static function fromEntity(TaskHasFile $taskHasFile): self
    {
        return new self(
            id: $taskHasFile->getId(),
            task: $taskHasFile->getTask()?->getId(),
            local_name: $taskHasFile->getLocalName(),
            global_name: $taskHasFile->getGlobalName(),
            user: $taskHasFile->getUserId(),
            createdAt: $taskHasFile->getCreatedAt()->format(\DateTime::ATOM),
        );
    }
}
