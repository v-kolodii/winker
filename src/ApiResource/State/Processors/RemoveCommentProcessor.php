<?php

declare(strict_types=1);

namespace App\ApiResource\State\Processors;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\State\Providers\UserTrait;
use App\Doctrine\CompanyEntityManager;
use App\Entity\TaskHasComment;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

final readonly class RemoveCommentProcessor implements ProcessorInterface
{
    use UserTrait;

    public function __construct(
        private ManagerRegistry      $managerRegistry,
        private Security             $security,
        private CompanyEntityManager $companyEntityManagerService,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        $user = $this->getUser();
        $newManager = $this->getNewManager($user);
        $comment = $newManager->getRepository(TaskHasComment::class)->find($data->id);
        $comment = $newManager->getReference(TaskHasComment::class, $comment->getId());
        $newManager->remove($comment);
        $newManager->flush();
    }
}
