<?php

namespace App\EventSubscriber;

use App\Entity\Company;
use App\Service\CompanyService;
use Doctrine\DBAL\Exception;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class CompanyCreatedSubscriber implements EventSubscriberInterface
{
    public function __construct(private CompanyService $companyService)
    {
    }
    /**
     * @throws Exception
     */
    public function onAfterEntityPersistedEvent($event): void
    {
        $entity = $event->getEntityInstance();

        if (!($entity instanceof Company)) {
            return;
        }

        $this->companyService->addNewDbForCompany($entity);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AfterEntityPersistedEvent::class => ['onAfterEntityPersistedEvent'],
        ];
    }
}
