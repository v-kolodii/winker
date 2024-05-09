<?php

namespace App\ApiResource\State\Providers;

use ApiPlatform\Exception\RuntimeException;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

trait UserTrait
{
    private function getUser()
    {
        /**@var UserInterface $user */
        $user = $this->security->getUser();
        if (! $user->getCompany()) {
            throw new RuntimeException(sprintf('%s. User ID: %d must have a company', __CLASS__, $user->getId()));
        }

        return $user;
    }

    private function getNewManager(UserInterface $user): EntityManagerInterface
    {
        /** @var EntityManagerInterface $manager */
        $manager = $this->managerRegistry->getManagerForClass(User::class);
        $connection = $manager->getConnection();
        $connection->changeDatabase($user->getCompany()->getDbUrl());
        /** @var EntityManagerInterface $newManager */
        $newManager = $this->companyEntityManagerService->getEntityManager();

        return $newManager;
    }
}
