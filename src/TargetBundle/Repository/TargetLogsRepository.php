<?php

namespace TargetBundle\Repository;

/**
 * Class TargetLogsRepository.
 *
 * @author Vitaly Dergunov
 */
class TargetLogsRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param null|string $clientName
     * @param int         $limit
     *
     * @return mixed
     */
    public function getLogsByClientName(string $clientName = null, $limit = 20)
    {
        return
            $this->createQueryBuilder('cl')
                ->select('cl')
                ->where('cl.clientName = :clientName')
                ->orderBy('cl.id', 'DESC')
                ->setParameter('clientName', $clientName)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();
    }

    /**
     * @param null|string $requestPath
     * @param int         $limit
     *
     * @return mixed
     */
    public function getLogByPathInfo(string $requestPath = null, $limit = 20)
    {
        return
            $this->createQueryBuilder('cg')
                ->select('cg')
                ->where('cl.requestPath = :requestPath')
                ->orderBy('cg.id', 'DESC')
                ->setParameter('requestPath', $requestPath)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();
    }

    /**
     * @param null|string $dateFrom
     * @param null|string $dateTo
     * @param int         $limit
     *
     * @return mixed
     */
    public function getLogsByDateTime(string $dateFrom = null, string $dateTo = null, $limit = 20)
    {
        return
            $this->createQueryBuilder('cg')
                ->select('cg')
                ->where('cg.requestDatetime BETWEEN :dateFrom AND :dateTo')
                ->orderBy('cg.id', 'DESC')
                ->setParameter('dateFrom', $dateFrom)
                ->setParameter('dateTo', $dateTo)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();
    }

    /**
     * @param null|string $requestMethod
     * @param int         $limit
     *
     * @return mixed
     */
    public function getLogsByRequestMethod(string $requestMethod = null, $limit = 20)
    {
        return
            $this->createQueryBuilder('cg')
                ->select('cg')
                ->where('cg.requestMethod = :requestMethod')
                ->orderBy('cg.id', 'DESC')
                ->setParameter('requestMethod', $requestMethod)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();
    }
}
