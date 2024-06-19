<?php

namespace App\DTO;

use App\Entity\UserDevice;
use Symfony\Component\Serializer\Annotation\Groups;

class UserDeviceDTO
{
    public function __construct(
        #[Groups(['user:device:read'])]
        public ?string $deviceToken = null,
    ){}

    public static function fromEntity(UserDevice $userDevice): self
    {
        return new self(
            $userDevice->getDeviceToken(),
        );
    }
}
