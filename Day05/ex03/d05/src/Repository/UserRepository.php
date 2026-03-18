<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

       /**
         * @return User[] Returns an array of User objects
         */
        public function findByUsername($value): ?User
        {
            return $this->createQueryBuilder('u')
                ->andWhere('u.username = :val')
		->setParameter('val', $value)
                ->getQuery()
                ->getOneOrNullResult()
            ;
        }

        public function findByEmail($value): ?User
        {
            return $this->createQueryBuilder('u')
                ->andWhere('u.email = :val')
                ->setParameter('val', $value)
                ->getQuery()
                ->getOneOrNullResult()
            ;
        }
}
