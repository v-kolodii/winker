<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use App\ApiResource\State\Processors\NotificationProcessor;
use App\ApiResource\State\Processors\NotificationRemoveProcessor;
use App\ApiResource\State\Providers\NotificationCollectionProvider;
use App\ApiResource\State\Providers\NotificationPerformerCollectionProvider;
use App\ApiResource\State\Providers\NotificationProvider;
use App\Repository\NotificationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ApiResource(
        operations: [
            new GetCollection(
//                uriTemplate: '/notifications',
                openapi: new Operation(
                    summary: 'Get notifications',
                    description: 'Use this endpoint to get notifications',
                ),
                paginationEnabled: true,
                paginationItemsPerPage: 20,
                description: '# Get notifications',
                normalizationContext: ['groups' => 'notifications:list'],
                provider: NotificationCollectionProvider::class,
            ),
            new GetCollection(
                uriTemplate: '/notifications/{performer_id}',
                uriVariables: ['performer_id'],
                openapiContext: [
                    'parameters' => [
                        [
                            'name' => 'performer_id',
                            'in' => 'path',
                            'description' => 'Performer ID',
                            'required' => true,
                            'schema' => ['type' => 'integer'],
                        ],
                    ],
                ],
                openapi: new Operation(
                    summary: 'Get notifications for user',
                    description: 'Use this endpoint to get notifications user',
                ),
                paginationEnabled: true,
                paginationItemsPerPage: 20,
                description: '# Get notifications for user',
                normalizationContext: ['groups' => 'notifications:list'],
                provider: NotificationPerformerCollectionProvider::class,
            ),
//            new Get(
//                normalizationContext: ['groups' => 'notifications:read'],
//                provider: NotificationProvider::class),
            new Post(
                read: false,
                processor: NotificationProcessor::class
            ),
//            new Patch(
//                normalizationContext: ['groups' => 'notifications:update:read'],
//                denormalizationContext: ['groups' => 'notifications:update'],
//                input: NotificationDTO::class,
//                output: NotificationDTO::class,
//                read: false,
//                processor: NotificationUpdateProcessor::class,
//            ),
            new Delete(
                provider: NotificationProvider::class,
                processor: NotificationRemoveProcessor::class
            ),
        ],
    normalizationContext: [
        'groups' => ['notifications:read'],
    ],
    denormalizationContext: [
        'groups' => ['notifications:write'],
    ],
)]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['notifications:list', 'notifications:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['notifications:list', 'notifications:read', 'notifications:write'])]
    private ?string $type = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['notifications:list', 'notifications:read'])]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(type: 'integer',nullable: true)]
    #[Groups(['notifications:list', 'notifications:read', 'notifications:write'])]
    private ?int $data_id = null;

    #[ORM\Column]
    #[Groups(['notifications:list', 'notifications:read'])]
    private ?int $user_id = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['notifications:list', 'notifications:read', 'notifications:write'])]
    private ?int $performer_id = null;

    public function __construct()
    {
        $this->created_at = new \DateTime();
    }

    public function __toString(): string
    {
        return $this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
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

    public function getDataId(): ?int
    {
        return $this->data_id;
    }

    public function setDataId(?int $data_id): static
    {
        $this->data_id = $data_id;

        return $this;
    }
}
