<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use App\ApiResource\State\Processors\PersistProcessor;
use App\ApiResource\State\Processors\TaskUpdateProcessor;
use App\ApiResource\State\Providers\TaskAssignedToMeCollectionProvider;
use App\ApiResource\State\Providers\TaskCreatedByMeCollectionProvider;
use App\ApiResource\State\Providers\TaskItemProvider;
use App\ApiResource\State\Providers\TaskProvider;
use App\DTO\TaskDTO;
use App\Entity\Enum\Status;
use App\Entity\Enum\TaskType;
use App\Entity\Enum\WinkType;
use App\Repository\TaskRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[ApiResource(
        operations: [
            new GetCollection(
                uriTemplate: '/tasks/my-tasks',
                openapi: new Operation(
                    summary: 'Get tasks created by current user',
                    description: 'Use this endpoint to get tasks created by current user',
                ),
                paginationEnabled: true,
                paginationItemsPerPage: 20,
                description: '# Get tasks created by current user',
                normalizationContext: ['groups' => 'task:list'],
                provider: TaskCreatedByMeCollectionProvider::class,
            ),
            new GetCollection(
                uriTemplate: '/tasks/assigned-to-me',
                openapi: new Operation(
                    summary: 'Get tasks assigned to current user',
                    description: 'Use this endpoint to get tasks assigned to user',
                ),
                paginationEnabled: true,
                paginationItemsPerPage: 20,
                description: '# Get tasks assigned to current user',
                normalizationContext: ['groups' => 'task:list'],
                provider: TaskAssignedToMeCollectionProvider::class,
            ),
            new Get(
                normalizationContext: ['groups' => 'task:read'],
                provider: TaskProvider::class),
//            new GetCollection(
//                paginationEnabled: false,
//                normalizationContext: ['groups' => 'task:list'],
//                provider: TaskCollectionProvider::class),
            new Post(
                processor: PersistProcessor::class
            ),
            new Patch(
                normalizationContext: ['groups' => 'task:update:read'],
                denormalizationContext: ['groups' => 'task:update'],
                input: TaskDTO::class,
                output: TaskDTO::class,
                read: false,
                processor: TaskUpdateProcessor::class,
            ),
//            new Delete(
//                processor: RemoveProcessor::class
//            ),
        ],
    normalizationContext: [
        'groups' => ['task:read'],
    ],
    denormalizationContext: [
        'groups' => ['task:write'],
    ],
    order: ['wink_type' => 'DESC'],
)]
#[ORM\HasLifecycleCallbacks]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['task:list', 'task:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['task:list', 'task:read', 'task:write'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['task:list', 'task:read', 'task:write'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['task:list', 'task:read', 'task:write'])]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(
        type: 'smallint',
        nullable: true,
        enumType: TaskType::class,
        options: ['comment' => '0:Typical, 1:Regular'])]
    #[Groups(['task:list', 'task:read', 'task:write'])]
    private TaskType $task_type = TaskType::TASK_TYPE_TYPICAL;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['task:list', 'task:read', 'task:write'])]
    private ?\DateTimeInterface $type_base_plane_date = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['task:list', 'task:read', 'task:write'])]
    private ?\DateTimeInterface $type_reg_daily_finished_time = null;

    #[ORM\Column(length: 8, nullable: true)]
    #[Groups(['task:list', 'task:read', 'task:write'])]
    private ?string $type_reg_weekly_day = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['task:list', 'task:read', 'task:write'])]
    private ?\DateTimeInterface $type_reg_weekly_time = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['task:list','task:read', 'task:write'])]
    private ?int $type_reg_month_day = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['task:list', 'task:read', 'task:write'])]
    private ?\DateTimeInterface $type_reg_month_time = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['task:list', 'task:read', 'task:write'])]
    private ?\DateTimeInterface $finished_date = null;

    #[ORM\Column(
        type: 'smallint',
        nullable: true,
        enumType: WinkType::class,
        options: ['comment' => '0:Medium, 1:High, 2:Asap'])]
    #[Groups(['task:list', 'task:read', 'task:write'])]
    private WinkType $wink_type = WinkType::WINK_TYPE_MEDIUM;

    #[ORM\Column(
        type: 'smallint',
        nullable: true,
        enumType: Status::class,
        options: ['comment' => '0:New, 1:InProgress, 2:Finished'])]
    #[Groups(['task:list', 'task:read', 'task:write'])]
    private Status $status = Status::NEW;

    #[ORM\Column]
    #[Groups(['task:list', 'task:read', 'task:write'])]
    private ?int $user_id = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['task:list', 'task:read', 'task:write'])]
    private ?int $performer_id = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'tasks')]
    #[Groups(['task:list', 'task:read', 'task:write'])]
    private ?self $parent = null;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class)]
    #[Groups(['task:list', 'task:read'])]
    private Collection $tasks;

    #[ORM\OneToMany(mappedBy: 'task', targetEntity: TaskHasComment::class)]
    #[Groups(['task:list', 'task:read'])]
    private Collection $taskHasComments;

    #[ORM\OneToMany(mappedBy: 'task', targetEntity: TaskHasFile::class)]
    #[Groups(['task:list', 'task:read'])]
    private Collection $taskHasFiles;

    #[ORM\Column]
    #[Groups(['task:list', 'task:read', 'task:write'])]
    private bool $list_enable = false;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
        $this->taskHasComments = new ArrayCollection();
        $this->taskHasFiles = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $createdDate): static
    {
        $this->created_at = $createdDate;

        return $this;
    }

    public function getTaskType(): TaskType
    {
        return $this->task_type;
    }

    public function setTaskType(TaskType $task_type): static
    {
        $this->task_type = $task_type;

        return $this;
    }

    public function getTypeBasePlaneDate(): ?\DateTimeInterface
    {
        return $this->type_base_plane_date;
    }

    public function setTypeBasePlaneDate(?\DateTimeInterface $type_base_plane_date): static
    {
        $this->type_base_plane_date = $type_base_plane_date;

        return $this;
    }

    public function getTypeRegDailyFinishedTime(): ?\DateTimeInterface
    {
        return $this->type_reg_daily_finished_time;
    }

    public function setTypeRegDailyFinishedTime(\DateTimeInterface $type_reg_daily_finished_time): static
    {
        $this->type_reg_daily_finished_time = $type_reg_daily_finished_time;

        return $this;
    }

    public function getTypeRegWeeklyDay(): ?string
    {
        return $this->type_reg_weekly_day;
    }

    public function setTypeRegWeeklyDay(?string $type_reg_weekly_day): static
    {
        $this->type_reg_weekly_day = $type_reg_weekly_day;

        return $this;
    }

    public function getTypeRegWeeklyTime(): ?\DateTimeInterface
    {
        return $this->type_reg_weekly_time;
    }

    public function setTypeRegWeeklyTime(?\DateTimeInterface $type_reg_weekly_time): static
    {
        $this->type_reg_weekly_time = $type_reg_weekly_time;

        return $this;
    }

    public function getTypeRegMonthDay(): ?int
    {
        return $this->type_reg_month_day;
    }

    public function setTypeRegMonthDay(?int $type_reg_month_day): static
    {
        $this->type_reg_month_day = $type_reg_month_day;

        return $this;
    }

    public function getTypeRegMonthTime(): ?\DateTimeInterface
    {
        return $this->type_reg_month_time;
    }

    public function setTypeRegMonthTime(?\DateTimeInterface $type_reg_month_time): static
    {
        $this->type_reg_month_time = $type_reg_month_time;

        return $this;
    }

    public function getFinishedDate(): ?\DateTimeInterface
    {
        return $this->finished_date;
    }

    public function setFinishedDate(?\DateTimeInterface $finished_date): static
    {
        $this->finished_date = $finished_date;

        return $this;
    }

    public function getWinkType(): WinkType
    {
        return $this->wink_type;
    }

    public function setWinkType(WinkType $wink_type): static
    {
        $this->wink_type = $wink_type;

        return $this;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function setStatus(Status $status): static
    {
        $this->status = $status;

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

    public function getPerformerId(): ?int
    {
        return $this->performer_id;
    }

    public function setPerformerId(?int $performer_id): static
    {
        $this->performer_id = $performer_id;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(self $task): static
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
            $task->setParent($this);
        }

        return $this;
    }

    public function removeTask(self $task): static
    {
        if ($this->tasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getParent() === $this) {
                $task->setParent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TaskHasComment>
     */
    public function getTaskHasComments(): Collection
    {
        return $this->taskHasComments;
    }

    public function addTaskHasComment(TaskHasComment $taskHasComment): static
    {
        if (!$this->taskHasComments->contains($taskHasComment)) {
            $this->taskHasComments->add($taskHasComment);
            $taskHasComment->setTask($this);
        }

        return $this;
    }

    public function removeTaskHasComment(TaskHasComment $taskHasComment): static
    {
        if ($this->taskHasComments->removeElement($taskHasComment)) {
            // set the owning side to null (unless already changed)
            if ($taskHasComment->getTask() === $this) {
                $taskHasComment->setTask(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TaskHasFile>
     */
    public function getTaskHasFiles(): Collection
    {
        return $this->taskHasFiles;
    }

    public function addTaskHasFile(TaskHasFile $taskHasFile): static
    {
        if (!$this->taskHasFiles->contains($taskHasFile)) {
            $this->taskHasFiles->add($taskHasFile);
            $taskHasFile->setTask($this);
        }

        return $this;
    }

    public function removeTaskHasFile(TaskHasFile $taskHasFile): static
    {
        if ($this->taskHasFiles->removeElement($taskHasFile)) {
            // set the owning side to null (unless already changed)
            if ($taskHasFile->getTask() === $this) {
                $taskHasFile->setTask(null);
            }
        }

        return $this;
    }

    public function toArray(): array
    {
        return [
            "id" => $this->getId(),
            "title" => $this->getTitle(),
            "description" => $this->getDescription(),
            "task_type" => $this->getTaskType()->value,
            "type_base_plane_date" => $this->getTypeBasePlaneDate()?->format(DateTimeInterface::ATOM),
            "type_reg_daily_finished_time" => $this->getTypeRegDailyFinishedTime()?->format(DateTimeInterface::ATOM),
            "type_reg_weekly_day" => $this->getTypeRegWeeklyDay(),
            "type_reg_weekly_time" => $this->getTypeRegWeeklyTime()?->format(DateTimeInterface::ATOM),
            "type_reg_month_day" => $this->getTypeRegMonthDay(),
            "type_reg_month_time" => $this->getTypeRegMonthTime()?->format(DateTimeInterface::ATOM),
            "finished_date" => $this->getFinishedDate()?->format(DateTimeInterface::ATOM),
            "wink_type" => $this->getWinkType()->value,
            "status" => $this->getStatus()->value,
            "user_id" => $this->getUserId(),
            "performer_id" => $this->getPerformerId(),
            "parent" => $this->getParent()?->getId(),
            "list_enable" => $this->getListEnable(),
            "created_at" => $this->getCreatedAt()->format(DateTimeInterface::ATOM),
        ];
    }

    public function getListEnable(): bool
    {
        return $this->list_enable;
    }

    public function setListEnable(bool $list_enable): void
    {
        $this->list_enable = $list_enable;
    }

    public function getNewNotificationTitle(): string
    {
        return 'New Task!';
    }

    public function getUpdatedNotificationTitle(): string
    {
        return 'Task was updated!';
    }

    public function getMessageType(): string
    {
        return 'task';
    }
}
