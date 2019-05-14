<?php

namespace TargetBundle\Service;

use Doctrine\ORM\EntityManager;
use ProxyBundle\Event\ProxyRequestEvent;
use TargetBundle\Entity\TargetLogs;

/**
 * @author Vitaly Dergunov
 */
class TargetLogManager
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * TargetLogManager constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param ProxyRequestEvent $proxyRequestEvent
     * @param int               $limit
     *
     * @return mixed
     */
    public function fetchLogsByClientName(ProxyRequestEvent $proxyRequestEvent, $limit = 10)
    {
        return $this->entityManager
            ->getRepository(\TargetBundle\Entity\TargetLogs::class)
            ->getLogByClientName($proxyRequestEvent->getClientName(), $limit);
    }

    /**
     * @param ProxyRequestEvent $proxyRequestEvent
     * @param int               $limit
     *
     * @return mixed
     */
    public function fetchLogsByPathInfo(ProxyRequestEvent $proxyRequestEvent, $limit = 10)
    {
        return $this->entityManager
            ->getRepository(\TargetBundle\Entity\TargetLogs::class)
            ->getLogByPathInfo($proxyRequestEvent->getRequest()->pathInfo(), $limit);
    }

    /**
     * @param ProxyRequestEvent $proxyRequestEvent
     * @param int               $limit
     *
     * @return mixed
     */
    public function fetchLogsByRequestMethod(ProxyRequestEvent $proxyRequestEvent, $limit = 10)
    {
        return $this->entityManager
            ->getRepository(\TargetBundle\Entity\TargetLogs::class)
            ->getLogsByRequestMethod($proxyRequestEvent->getRequest()->method(), $limit);
    }

    /**
     * @param null|string $dateFrom
     * @param null|string $dateTo
     * @param int         $limit
     *
     * @return mixed
     */
    public function fetchLogsByDateTime(string $dateFrom = null, string $dateTo = null, $limit = 10)
    {
        return $this->entityManager
            ->getRepository(\TargetBundle\Entity\TargetLogs::class)
            ->getLogByDateTime($dateFrom, $dateTo, $limit);
    }

    /**
     * @param ProxyRequestEvent $proxyRequestEvent
     *
     * @return string
     */
    public function saveLogs(ProxyRequestEvent $proxyRequestEvent)
    {
        $logData = new TargetLogs();
        $logData->setRequestPath($proxyRequestEvent->getRequest()->pathInfo());
        $logData->setRequestMethod($proxyRequestEvent->getRequest()->method());

        $logData->setRequestPerSecondRemains(
            $proxyRequestEvent->getHeaders()['X-RateLimit-RPS-Remaining'][0] ?? null
        );

        $logData->setRequestPerMinuteRemains(
            $proxyRequestEvent->getHeaders()['X-RateLimit-Minutely-Remaining'][0] ?? null
        );
        $logData->setRequestPerHourRemains(
            $proxyRequestEvent->getHeaders()['X-RateLimit-Hourly-Remaining'][0] ?? null
        );
        $logData->setRequestPerDayRemains(
            $proxyRequestEvent->getHeaders()['X-RateLimit-Daily-Remaining'][0] ?? null
        );

        $clientName = $proxyRequestEvent->getClientName();

        if (!$clientName) {
            $clientName = $proxyRequestEvent->getAgencyName();
        }

        $logData->setClientName($clientName);

        $this->entityManager->persist($logData);
        $this->entityManager->flush();
    }
}
