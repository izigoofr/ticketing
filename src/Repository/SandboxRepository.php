<?php

namespace App\Repository;

use App\Entity\Sandbox;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sandbox>
 *
 * @method Sandbox|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sandbox|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sandbox[]    findAll()
 * @method Sandbox[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SandboxRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sandbox::class);
    }

//    /**
//     * @return Sandbox[] Returns an array of Sandbox objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Sandbox
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
