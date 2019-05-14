<?php

namespace TargetBundle\Repository;

/**
 * Class TargetAgencyTokenRepository.
 */
class TargetAgencyTokenRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param null|string $refreshToken
     * @param null|string $accessToken
     *
     * @return mixed
     */
    public function updateAgencyToken(string $refreshToken = null, string $accessToken = null)
    {
        return
            $this->createQueryBuilder('agt')
                ->update()
                ->set('agt.accessToken', ':accessToken')
                ->set('agt.lastUpdate', ':lastUpdate')
                ->where('agt.refreshToken = :refreshToken')
                ->setParameter('refreshToken', $refreshToken)
                ->setParameter('accessToken', $accessToken)
                ->setParameter('lastUpdate', date('Y-m-d H:i:s'))
                ->getQuery()
                ->getResult();
    }
}
