<?php

namespace App\ApiResource\State\Providers;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

readonly class UserInfoProvider implements ProviderInterface
{
    public function __construct(private Security $security,)
    {

    }
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): UserInterface|null
    {
        /**@var UserInterface $user */
        return $this->security->getUser();
    }
}
