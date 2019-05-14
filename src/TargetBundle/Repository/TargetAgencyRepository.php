<?php

namespace TargetBundle\Repository;

/**
 * Class TargetAgencytRepository.
 */
class TargetAgencyRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @return array
     */
    public function getAgencyByName(string $agencyName = null)
    {
        return
            $this->createQueryBuilder('ag')
                ->select('ag, comp')
                ->leftJoin('ag.companies', 'comp')
                ->where('ag.agencyName = :agencyName')
                ->andWhere('ag.isSandbox = :isSandbox')
                ->setParameter('agencyName', $agencyName)
                ->setParameter('isSandbox', false)
                ->getQuery()
                ->getOneOrNullResult();
    }
}
