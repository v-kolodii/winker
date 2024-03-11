<?php

namespace App\ApiResource\State\Providers;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class UserCompanyProvider implements ProviderInterface
{
    public function __construct(
        private Security       $security,
        private UserRepository $repository,
    ) {

    }
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|null
    {
        /**@var UserInterface $user */
        $user = $this->security->getUser();
        if (! $user->getCompany()) {
            return [$user];
        }

        return $this->repository->findByCompanyId($user->getCompany()->getId());
    }
}
