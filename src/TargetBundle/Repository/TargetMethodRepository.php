<?php

namespace TargetBundle\Repository;

/**
 * Class TargetMethodRepository.
 *
 * @author Vitaly Dergunov
 */
class TargetMethodRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param null|string $companyName
     *
     * @return mixed
     */
    public function getMethodsByCompany(string $companyName = null)
    {
        return
            $this->createQueryBuilder('mt')
                ->select('mt.methodName')
                ->leftJoin('mt.companies', 'cm')
                ->where('cm.companyName = :companyName')
                ->setParameter('companyName', $companyName)
                ->getQuery()
                ->getArrayResult();
    }
}
