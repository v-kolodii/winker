<?php

namespace App\Repository;

use App\Entity\TaskHasComment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TaskHasComment>
 *
 * @method TaskHasComment|null find($id, $lockMode = null, $lockVersion = null)
 * @method TaskHasComment|null findOneBy(array $criteria, array $orderBy = null)
 * @method TaskHasComment[]    findAll()
 * @method TaskHasComment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskHasCommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaskHasComment::class);
    }

//    /**
//     * @return TaskHasComment[] Returns an array of TaskHasComment objects
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

//    public function findOneBySomeField($value): ?TaskHasComment
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
