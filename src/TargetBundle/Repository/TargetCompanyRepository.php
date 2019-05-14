<?php

namespace TargetBundle\Repository;

class TargetCompanyRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param string|null $companyName
     * @return mixed
     */
    public function getCompanyByName(string $companyName = null)
    {
        return
            $this->createQueryBuilder('cm')
                ->select('cm')
                ->where('cm.companyName=:companyName')
                ->setParameter('companyName', $companyName)
                ->getQuery()
                ->getOneOrNullResult();
    }

    /**
     * @param string|null $companyName
     * @return mixed
     */
    public function getClientsByCompany(string $companyName = null)
    {
        return $this->createQueryBuilder('cm')
            ->select('cl.clientName')
            ->leftJoin('cm.agencies', 'ag')
            ->leftJoin('ag.clients', 'cl')
            ->where('cm.companyName = :companyName')
            ->setParameter('companyName', $companyName)
            ->getQuery()
            ->getResult();
    }
}
