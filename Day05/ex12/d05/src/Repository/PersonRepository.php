<?php

namespace App\Repository;

use App\Entity\Person;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PersonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
    }

    public function findPersons(
        ?bool $adult,
        string $sort,
        string $order
    ): array {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.address', 'a')
            ->addSelect('a')
            ->leftJoin('p.bankAccount', 'b')
            ->addSelect('b');

        if ($adult !== null) {
            $qb->andWhere('p.adult = :adult')
               ->setParameter('adult', $adult);
        }

        $qb->orderBy('p.' . $sort, $order);

        return $qb->getQuery()->getResult();
    }
}