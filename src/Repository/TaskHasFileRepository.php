<?php

namespace App\Repository;

use App\Entity\TaskHasFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TaskHasFile>
 *
 * @method TaskHasFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method TaskHasFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method TaskHasFile[]    findAll()
 * @method TaskHasFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskHasFileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaskHasFile::class);
    }

//    /**
//     * @return TaskHasFile[] Returns an array of TaskHasFile objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TaskHasFile
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
