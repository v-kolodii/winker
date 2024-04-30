<?php

namespace App\ApiResource\State\Providers;

use ApiPlatform\Exception\RuntimeException;
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
}
