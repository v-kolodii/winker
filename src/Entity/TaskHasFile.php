<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use App\ApiResource\State\Processors\RemoveFileProcessor;
use App\ApiResource\State\Processors\TaskHasFileCreateProcessor;
use App\ApiResource\State\Providers\TaskHasFileProvider;
use App\ApiResource\State\Providers\TaskHasFilesProvider;
use App\DTO\TasksFileDTO;
use App\Repository\TaskHasFileRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Google\Type\DateTime;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TaskHasFileRepository::class)]
#[ApiResource(
    uriTemplate: '/tasks/{taskId}/files',
    shortName: 'Task Files',
    operations: [
        new GetCollection(
            uriVariables: ['taskId' => new Link(fromProperty: 'taskHasFiles', fromClass: Task::class)],
            openapiContext: [
                'parameters' => [
                    [
                        'name' => 'taskId',
                        'in' => 'path',
                        'description' => 'Task ID',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
            ],
            normalizationContext: ['groups' => 'file:collection:read'],
            output: TasksFileDTO::class,
            provider: TaskHasFilesProvider::class
        ),
        new Get(
            uriTemplate: '/tasks/{taskId}/files/{fileId}',
            uriVariables: [
                'taskId' => new Link(fromProperty: 'taskHasFiles', fromClass: Task::class),
                'fileId' => new Link(fromProperty: 'id', fromClass: TaskHasFile::class)],
            openapiContext: [
                'parameters' => [
                    [
                        'name' => 'taskId',
                        'in' => 'path',
                        'description' => 'Task ID',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                    [
                        'name' => 'fileId',
                        'in' => 'path',
                        'description' => 'File ID',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
            ],
            normalizationContext: ['groups' => 'file:read'],
            output: TasksFileDTO::class,
            provider: TaskHasFileProvider::class
        ),
        new Post(
            uriVariables: ['taskId'],
            openapiContext: [
                'parameters' => [
                    [
                        'name' => 'taskId',
                        'in' => 'path',
                        'description' => 'Task ID',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
            ],
            normalizationContext: ['groups' => 'file:read'],
            denormalizationContext: ['groups' => 'file:write'],
            input: TasksFileDTO::class,
            output: TasksFileDTO::class,
            read: false,
            processor: TaskHasFileCreateProcessor::class
        ),
        new Delete(
            uriTemplate: '/tasks/{taskId}/files/{fileId}',
            uriVariables: ['taskId', 'fileId'],
            openapiContext: [
                'parameters' => [
                    [
                        'name' => 'taskId',
                        'in' => 'path',
                        'description' => 'Task ID',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                    [
                        'name' => 'fileId',
                        'in' => 'path',
                        'description' => 'File ID',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
            ],
            provider: TaskHasFileProvider::class,
            processor: RemoveFileProcessor::class
        ),
    ],
    paginationEnabled: false,
)]
#[ORM\HasLifecycleCallbacks]
class TaskHasFile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['task:list', 'task:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'taskHasFiles')]
    private ?Task $task = null;

    #[ORM\Column(length: 1024, nullable: true)]
    #[Groups(['task:list', 'task:read'])]
    private ?string $local_name = null;

    #[ORM\Column(length: 1024, nullable: true)]
    #[Groups(['task:list', 'task:read'])]
    private ?string $global_name = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['task:list', 'task:read'])]
    private ?int $user_id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['task:list', 'task:read'])]
    private ?\DateTimeInterface $created_at = null;

    public function __construct()
    {
//        $this->created_at = new \DateTime('now');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTask(): ?Task
    {
        return $this->task;
    }

    public function setTask(?Task $task): static
    {
        $this->task = $task;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(?int $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function getLocalName(): ?string
    {
        return $this->local_name;
    }

    public function setLocalName(?string $localName): static
    {
        $this->local_name = $localName;

        return $this;
    }

    public function getGlobalName(): ?string
    {
        return $this->global_name;
    }

    public function setGlobalName(?string $globalName): static
    {
        $this->global_name = $globalName;

        return $this;
    }

    public function getNewNotificationTitle(): string
    {
        return 'New File!';
    }

    public function getUpdatedNotificationTitle(): string
    {
        return '';
    }

    public function getMessageType(): string
    {
        return 'file';
    }

    public function toArray(): array
    {
        return [
            "id" => $this->getId(),
            "task" => $this->getTask()->getId(),
            "local_name" => $this->getLocalName(),
            "global_name" => $this->getGlobalName(),
            "user" => $this->getUserId(),
            "created_at" => $this->getCreatedAt()?->format(DateTimeInterface::ATOM),
        ];
    }

    public function setCreatedAt(?DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }
}
