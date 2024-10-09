<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\Partner;
use App\Entity\PartnerCompany;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PartnerCompany>
 */
class PartnerCompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PartnerCompany::class);
    }

    public function findOneById($id): ?PartnerCompany
    {
        return $this->createQueryBuilder('pa')
            ->andWhere('pa.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
    
    public function getTotalParticipationByCompany($company): float
    {
        $qb = $this->createQueryBuilder('pa')
                    ->andWhere('pa.company_id = :val')
                    ->setParameter('val', $company)
                    ->select('SUM(pa.participation)');

        return (float) $qb->getQuery()->getSingleScalarResult();
    }

    public function checkPartnerCompanyExists($data): ?PartnerCompany
    {
        return $this->createQueryBuilder('pa')
            ->andWhere('pa.company_id = :pid')
            ->andWhere('pa.partner_id = :cid')
            ->setParameter('pid', $data['partner_id'])
            ->setParameter('cid', $data['company_id'])
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findPartnerByCompany(Company $company): array
    {
        return $this->createQueryBuilder('pa')
            ->select(['pa.id as PC_ID', 'p.name', 'p.last_name', 'p.cpf', 'p.phone_number', 'pa.participation'])
            ->innerJoin('pa.partner_id', 'p')
            ->where('pa.company_id = :cId')
            ->setParameter('cId', $company->getId())
            ->getQuery()
            ->getResult();
    }

    
    public function findCompanyByPartner(Partner $partner): array
    {
        return $this->createQueryBuilder('pa')
            ->select(['pa.id as PC_ID', 'p.fantasy_name', 'p.company_name', 'p.cnpj', 'p.opening_date', 'p.phone_number', 'p.invoicing', 'pa.participation'])
            ->innerJoin('pa.company_id', 'p')
            ->where('pa.partner_id = :cId')
            ->setParameter('cId', $partner->getId())
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return PartnerCompany[] Returns an array of PartnerCompany objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?PartnerCompany
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
