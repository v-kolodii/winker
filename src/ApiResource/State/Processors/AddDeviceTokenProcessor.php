<?php

namespace App\ApiResource\State\Processors;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\State\Providers\UserTrait;
use App\Doctrine\CompanyEntityManager;
use App\DTO\UserDeviceDTO;
use App\Entity\User;
use App\Entity\UserDevice;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

class AddDeviceTokenProcessor implements ProcessorInterface
{
    use UserTrait;

    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly Security $security,
        private readonly CompanyEntityManager   $companyEntityManagerService,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): UserDeviceDTO
    {
        /**@var User $user*/
        $user = $this->getUser();
        $newManager = $this->getNewManager($user);

        $userDevice = $newManager->getRepository(UserDevice::class)->findOneBy([
            'userId' => $user->getId()
        ]);
        if (!$userDevice) {
            $userDevice = new UserDevice();
            $userDevice->setUserId($user->getId());
        }

        $userDevice->setDeviceToken($data->getDeviceToken());
        $newManager->persist($userDevice);
        $newManager->flush();

        return UserDeviceDTO::fromEntity($userDevice);
    }
}
