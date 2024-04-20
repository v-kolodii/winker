<?php

namespace App\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\MissingMappingDriverImplementation;
use Doctrine\Persistence\ManagerRegistry;

readonly class CompanyEntityManager
{
    public function __construct(private ManagerRegistry $managerRegistry)
    {
    }

    /**
     * @throws MissingMappingDriverImplementation
     */
    public function getEntityManager(): EntityManager
    {
        $em = $this->managerRegistry->getManager();
        /**@var Connection $connection */
        $connection = $this->managerRegistry->getConnection();

        return new EntityManager($connection, $em->getConfiguration(), $em->getEventManager());
    }
}
