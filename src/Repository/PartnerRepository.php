<?php

namespace App\Repository;

use App\Entity\Partner;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repositório de sócios
 */
class PartnerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Partner::class);
    }
    
    /**
     * Busca de sócios com paginas e busca
     */
    public function findPaginated(int $page = 1, int $limit = 10, $key = '', $search = ''): Paginator
    {
        $queryBuilder = $this->createQueryBuilder('p');

        if($key !== ''){
            $queryBuilder->where("p.$key like :param")
                        ->setParameter('param', "$search");
        }

        $queryBuilder->orderBy('p.id', 'ASC');

        $query = $queryBuilder->getQuery();
        $query->setFirstResult(($page - 1) * $limit)
              ->setMaxResults($limit);

        return new Paginator($query, true);
    }
    
    /**
     * Buscar sócio por ID
     */
    public function findOneById($id): ?Partner
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
    
    /**
     * Buscar empresa por CPF
     */
    public function findOneByCpf($cpf): ?Partner
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.cpf like :cpf')
            ->setParameter('cpf', $cpf)
            ->getQuery()
            ->getOneOrNullResult();
    }
    
    /**
     * Buscar empresa por E-mail
     */
    public function findOneByEmail($email): ?Partner
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.email like :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
