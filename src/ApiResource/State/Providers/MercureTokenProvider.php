<?php

namespace App\ApiResource\State\Providers;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Doctrine\CompanyEntityManager;
use App\DTO\MercureTokenDTO;
use App\Entity\UserDevice;
use Doctrine\Persistence\ManagerRegistry;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class MercureTokenProvider implements ProviderInterface
{
    use UserTrait;

    public function __construct(
        private readonly Security $security,
        private readonly ManagerRegistry $managerRegistry,
        private readonly CompanyEntityManager $companyEntityManagerService,
        private readonly ParameterBagInterface $parameterBag,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): MercureTokenDTO
    {
        $user = $this->getUser();

        $newManager = $this->getNewManager($user);

        $userDevice = $newManager->getRepository(UserDevice::class)->findOneBy([
            'userId' => $user->getId()
        ]);

        $deviceId = $userDevice->getDeviceToken();
        $secret = $this->parameterBag->get('mercure_secret');

        $config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText($secret)
        );

        $mercureToken = $config->builder()
            ->withClaim('mercure', [
                'subscribe' => ["/user/{$deviceId}/tasks"], // Топік для цього пристрою
            ])
            ->getToken($config->signer(), $config->signingKey());

        return new MercureTokenDTO($mercureToken->toString());
    }
}
