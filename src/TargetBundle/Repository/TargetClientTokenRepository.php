<?php

namespace TargetBundle\Repository;

/**
 * Class TargetClientTokenRepository.
 */
class TargetClientTokenRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param null|string $refreshToken
     * @param null|string $accessToken
     *
     * @return mixed
     */
    public function updateClientToken(string $refreshToken = null, string $accessToken = null)
    {
        return
            $this->createQueryBuilder('clt')
                ->update()
                ->set('clt.accessToken', ':accessToken')
                ->set('clt.lastUpdate', ':lastUpdate')
                ->where('clt.refreshToken = :refreshToken')
                ->setParameter('refreshToken', $refreshToken)
                ->setParameter('accessToken', $accessToken)
                ->setParameter('lastUpdate', date('Y-m-d H:i:s'))
                ->getQuery()
                ->getResult();
    }
}
