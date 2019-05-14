<?php

namespace TargetBundle\Repository;

/**
 * @author Vitaly Dergunov/
 */
class TargetHttpMethodRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param null|string $companyName
     *
     * @return mixed
     */
    public function getHttpMethodsByCompany(string $companyName = null)
    {
        return
            $this->createQueryBuilder('hmt')
                ->select('hmt.httpMethodName')
                ->leftJoin('hmt.companies', 'cm')
                ->where('cm.companyName = :companyName')
                ->setParameter('companyName', $companyName)
                ->getQuery()
                ->getArrayResult();
    }
}
