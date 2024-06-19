<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\ApiResource\State\Processors\AddDeviceTokenProcessor;
use App\DTO\UserDeviceDTO;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity()]
#[ORM\Table(name: 'user_device')]
#[ApiResource(
    operations: [
        new Post(
            normalizationContext: ['groups' => 'user:device:read'],
            denormalizationContext: ['groups' => ['user:device:write']],
            output: UserDeviceDTO::class,
            processor: AddDeviceTokenProcessor::class,
        ),
    ],
    normalizationContext: [
        'groups' => ['user:device:read'],
    ],
    denormalizationContext: [
        'groups' => ['user:device:write'],
    ],
)]
class UserDevice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $userId = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['user:device:write'])]
    private ?string $deviceToken = null;

    public function getDeviceToken(): ?string
    {
        return $this->deviceToken;
    }

    public function setDeviceToken(?string $deviceId): self
    {
        $this->deviceToken = $deviceId;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }
}
