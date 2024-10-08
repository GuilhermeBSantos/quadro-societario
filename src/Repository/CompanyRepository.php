<?php

namespace App\Repository;

use App\Entity\Company;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Company>
 */
class CompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Company::class);
    }

    //    /**
    //     * @return Company[] Returns an array of Company objects
    //     */

    /**
     * @param int $page
     * @param int $limit
     * @return Paginator
     */
    public function findPaginated(int $page = 1, int $limit = 10): Paginator
    {
        $queryBuilder = $this->createQueryBuilder('c')
                             ->orderBy('c.id', 'ASC');

        $query = $queryBuilder->getQuery();
        $query->setFirstResult(($page - 1) * $limit)
              ->setMaxResults($limit);

        return new Paginator($query, true);
    }

    
    public function findOneById($id): ?Company
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
    public function findOneByCnpj($cnpj): ?Company
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.cnpj like :cnpj')
            ->setParameter('cnpj', $cnpj)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
