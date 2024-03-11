<?php

namespace App\ApiResource\State\Providers;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\DepartmentRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class DepartmentCompanyProvider implements ProviderInterface
{
    public function __construct(
        private Security       $security,
        private DepartmentRepository $repository,
    ) {

    }
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|null
    {
        /**@var UserInterface $user */
        $user = $this->security->getUser();
        if (! $user->getCompany()) {
            return null;
        }

        return $this->repository->findByCompanyId($user->getCompany()->getId());
    }
}
