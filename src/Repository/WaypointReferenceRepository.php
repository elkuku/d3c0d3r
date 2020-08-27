<?php

namespace App\Repository;

use App\Entity\WaypointReference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method WaypointReference|null find($id, $lockMode = null, $lockVersion = null)
 * @method WaypointReference|null findOneBy(array $criteria, array $orderBy = null)
 * @method WaypointReference[]    findAll()
 * @method WaypointReference[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WaypointReferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WaypointReference::class);
    }

    // /**
    //  * @return WaypointReference[] Returns an array of WaypointReference objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('w.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?WaypointReference
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
