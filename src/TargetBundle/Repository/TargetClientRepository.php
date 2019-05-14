<?php

namespace TargetBundle\Repository;

/**
 * Class TargetClientRepository.
 */
class TargetClientRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param null|string $clientName
     *
     * @return mixed
     */
    public function getClientByName(string $clientName = null)
    {
        return
            $this->createQueryBuilder('cl')
                ->select('cl, ag, comp')
                ->leftJoin('cl.agency', 'ag')
                ->leftJoin('ag.companies', 'comp')
                ->where('cl.clientName = :clientName')
                ->setParameter('clientName', $clientName)
                ->getQuery()
                ->getOneOrNullResult();
    }

    /**
     * @param null|string $clientName
     *
     * @return mixed
     */
    public function getTokensByClientName(string $clientName = null)
    {
        return
            $this->createQueryBuilder('cl')
                ->select('cl, tk')
                ->leftJoin('cl.tokens', 'tk')
                ->where('cl.clientName = :clientName')
                ->setParameter('clientName', $clientName)
                ->getQuery()
                ->getResult();
    }

    /**
     * @param null|string $clientName
     *
     * @return mixed
     */
    public function getAgencyCompanyByClientName(string $clientName = null)
    {
        return
            $this->createQueryBuilder('cl')
                ->select('cl, ag, cm')
                ->leftJoin('cl.agency', 'ag')
                ->leftJoin('ag.companies', 'cm')
                ->where('cl.clientName = :clientName')
                ->setParameter('clientName', $clientName)
                ->getQuery()
                ->getResult();
    }
}
