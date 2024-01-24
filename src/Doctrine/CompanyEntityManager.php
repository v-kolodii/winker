<?php

namespace App\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\MissingMappingDriverImplementation;
use Doctrine\Persistence\ManagerRegistry;

class CompanyEntityManager
{
    public function __construct(private readonly ManagerRegistry $managerRegistry)
    {
    }

    /**
     * @throws MissingMappingDriverImplementation
     */
    public function getEntityManager(): EntityManager
    {
        $em = $this->managerRegistry->getManager();

        return new EntityManager($this->managerRegistry->getConnection(), $em->getConfiguration(), $em->getEventManager());
    }
}
