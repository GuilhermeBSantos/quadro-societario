<?php

namespace App\Repository;

use App\Entity\Company;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repositorio de empresas
 */
class CompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Company::class);
    }
    
    /**
     * Busca de empresa com paginas e busca
     */
    public function findPaginated(int $page = 1, int $limit = 10, $key = '', $search = ''): Paginator
    {
        $queryBuilder = $this->createQueryBuilder('c');

        if($key !== ''){
            $queryBuilder->where("c.$key like :param")
                        ->setParameter('param', "$search");
        }

        $queryBuilder->orderBy('c.id', 'ASC');

        $query = $queryBuilder->getQuery();
        $query->setFirstResult(($page - 1) * $limit)
              ->setMaxResults($limit);

        return new Paginator($query, true);
    }
    
    /**
     * Buscar empresa por ID
     */
    public function findOneById($id): ?Company
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
    
    /**
     * Buscar empresa por CNPJ
     */
    public function findOneByCnpj($cnpj): ?Company
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.cnpj like :cnpj')
            ->setParameter('cnpj', $cnpj)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
