<?php

namespace App\Repository;

use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Users>
 */
class UsersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Users::class);
    }

    //    /**
    //     * @return Users[] Returns an array of Users objects
    //     */
    
    /* public function findByExampleField($value): array
    {

    } */

    /**
     * @param int $page
     * @param int $limit
     * @return Paginator
     */
    public function findPaginated(int $page = 1, int $limit = 10): Paginator
    {
        $queryBuilder = $this->createQueryBuilder('u')
                             ->orderBy('u.id', 'ASC');

        $query = $queryBuilder->getQuery();
        $query->setFirstResult(($page - 1) * $limit)
              ->setMaxResults($limit);

        return new Paginator($query, true);
    }
    
    public function countUsersByFirstUse(): int
    {
        $fields = $this->createQueryBuilder('u')
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        return count($fields);
    }

    public function findOneById($value): ?Users
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.id = :id')
            ->setParameter('id', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    public function findOneByEmail($value): ?Users
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email = :mail')
            ->setParameter('mail', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
