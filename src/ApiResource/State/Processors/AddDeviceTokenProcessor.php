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
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AddDeviceTokenProcessor implements ProcessorInterface
{
    use UserTrait;

    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly Security $security,
        private readonly CompanyEntityManager $companyEntityManagerService,
        private readonly ParameterBagInterface $parameterBag,
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
        } else {
            $this->deleteOldQueue($userDevice->getDeviceToken());
        }

        $userDevice->setDeviceToken($data->getDeviceToken());
        $newManager->persist($userDevice);
        $newManager->flush();

        return UserDeviceDTO::fromEntity($userDevice);
    }

    private function deleteOldQueue(?string $deviceToken): void
    {
        $username = $this->parameterBag->get('messenger_user');
        $password = $this->parameterBag->get('messenger_pass');

        $connection = new AMQPStreamConnection('rabbitmq', 5672, $username, $password, '/');
        $channel = $connection->channel();

        $queueName = sprintf('user_queue_%s', $deviceToken);

        $queues = $channel->queue_declare($queueName, false, true, false, false);

        if (!empty($queues)) {
            $channel->queue_delete($queueName);
        }

        $channel->close();
        $connection->close();
    }
}
