<?php

namespace App\ApiResource\State\Processors;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\State\Providers\UserTrait;
use App\Doctrine\CompanyEntityManager;
use App\Entity\Notification;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

class NotificationProcessor implements ProcessorInterface
{
    use UserTrait;

    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly Security $security,
        private readonly CompanyEntityManager   $companyEntityManagerService,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Notification
    {
        $user = $this->getUser();
        $newManager = $this->getNewManager($user);

        $notification = (new Notification())
            ->setType($data->getType())
            ->setDataId($data->getDataId())
            ->setPerformerId($data->getPerformerId())
            ->setUserId($user->getId());
        $newManager->persist($notification);
        $newManager->flush();

        return $notification;
    }
}
