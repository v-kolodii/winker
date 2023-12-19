<?php

namespace App\Repository;

use App\Entity\CustomerRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CustomerRequest>
 *
 * @method CustomerRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method CustomerRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method CustomerRequest[]    findAll()
 * @method CustomerRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomerRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomerRequest::class);
    }

//    /**
//     * @return CustomerRequest[] Returns an array of CustomerRequest objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?CustomerRequest
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
