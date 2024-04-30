<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\ApiResource\State\Processors\CommentCreateProcessor;
use App\ApiResource\State\Processors\CommentUpdateProcessor;
use App\ApiResource\State\Processors\RemoveCommentProcessor;
use App\ApiResource\State\Providers\TaskHasCommentProvider;
use App\ApiResource\State\Providers\TaskHasCommentsProvider;
use App\DTO\CommentDTO;
use App\Repository\TaskHasCommentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TaskHasCommentRepository::class)]
#[ApiResource(
    uriTemplate: '/tasks/{taskId}/comments',
    shortName: 'Task Comments',
        operations: [
            new GetCollection(
                uriVariables: ['taskId' => new Link(fromProperty: 'taskHasComments', fromClass: Task::class)],
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
                normalizationContext: ['groups' => 'comment:collection:read'],
                output: CommentDTO::class,
                provider: TaskHasCommentsProvider::class
            ),
            new Get(
                uriTemplate: '/tasks/{taskId}/comments/{commentId}',
                uriVariables: ['taskId' => new Link(fromProperty: 'taskHasComments', fromClass: Task::class), 'commentId' => new Link(fromProperty: 'id', fromClass: TaskHasComment::class)],
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
                            'name' => 'commentId',
                            'in' => 'path',
                            'description' => 'Comment ID',
                            'required' => true,
                            'schema' => ['type' => 'integer'],
                        ],
                    ],
                ],
                normalizationContext: ['groups' => 'comment:read'],
                output: CommentDTO::class,
                provider: TaskHasCommentProvider::class
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
                normalizationContext: ['groups' => 'comment:read'],
                denormalizationContext: ['groups' => 'comment:write'],
                input: CommentDTO::class,
                output: CommentDTO::class,
                read: false,
                processor: CommentCreateProcessor::class
            ),
            new Put(
                uriTemplate: '/tasks/{taskId}/comments/{commentId}',
                uriVariables: ['taskId', 'commentId'],
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
                            'name' => 'commentId',
                            'in' => 'path',
                            'description' => 'Comment ID',
                            'required' => true,
                            'schema' => ['type' => 'integer'],
                        ],
                    ],
                ],
                normalizationContext: ['groups' => 'comment:read'],
                denormalizationContext: ['groups' => 'comment:write'],
                input: CommentDTO::class,
                output: CommentDTO::class,
                read: false,
                processor: CommentUpdateProcessor::class
            ),
            new Delete(
                uriTemplate: '/tasks/{taskId}/comments/{commentId}',
                uriVariables: ['taskId', 'commentId'],
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
                            'name' => 'commentId',
                            'in' => 'path',
                            'description' => 'Comment ID',
                            'required' => true,
                            'schema' => ['type' => 'integer'],
                        ],
                    ],
                ],
                provider: TaskHasCommentProvider::class,
                processor: RemoveCommentProcessor::class
            ),
        ],
    paginationEnabled: false,
)]
class TaskHasComment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['task:list', 'task:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Task::class, inversedBy: 'taskHasComments')]
    #[JoinColumn(name: 'task_id', referencedColumnName: 'id')]
    private ?Task $task = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['task:list', 'task:read'])]
    private ?string $comment = null;

    #[ORM\Column]
    #[Groups(['task:list', 'task:read'])]
    private ?int $user_id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['task:list', 'task:read'])]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['task:list', 'task:read'])]
    private ?\DateTimeInterface $updated_at = null;

    public function __construct()
    {
        $this->created_at = $this->updated_at = new \DateTime('now');
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

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }
}
