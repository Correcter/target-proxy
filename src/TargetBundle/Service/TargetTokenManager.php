<?php

namespace TargetBundle\Service;

use Doctrine\ORM\EntityManager;
use ProxyBundle\Event\ProxyRequestEvent;
use Symfony\Component\HttpFoundation\Response;
use TargetBundle\Entity\TargetAgency;
use TargetBundle\Entity\TargetAgencyToken;
use TargetBundle\Entity\TargetClient;
use TargetBundle\Entity\TargetClientToken;
use TargetBundle\Exceptions\ServiceIsLocked;
use TargetBundle\Model\TargetError;

/**
 * @author Vitaly Dergunov
 */
class TargetTokenManager
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var TargetTokenApi
     */
    private $targetTokenApi;

    /**
     * TargetTokenManager constructor.
     *
     * @param EntityManager  $entityManager
     * @param TargetTokenApi $targetTokenApi
     */
    public function __construct(
        EntityManager $entityManager,
        TargetTokenApi $targetTokenApi
    ) {
        $this->targetTokenApi = $targetTokenApi;
        $this->entityManager = $entityManager;
    }

    /**
     * @param ProxyRequestEvent $proxyRequestEvent
     *
     * @return null|string
     */
    public function getTargetToken(ProxyRequestEvent $proxyRequestEvent)
    {
        if ($proxyRequestEvent->getTargetToken()) {
            return $proxyRequestEvent->getTargetToken()->getAccessToken();
        }

        if ($proxyRequestEvent->getTargetClient()) {
            return $this->updateExpiredAndReturnActualClientToken(
                $proxyRequestEvent->getTargetClient()
            );
        }

        if ($proxyRequestEvent->getTargetAgency()) {
            return $this->updateExpiredAndReturnActualAgencyToken(
                $proxyRequestEvent->getTargetAgency()
            );
        }

        return null;
    }

    /**
     * @param ProxyRequestEvent $proxyRequestEvent
     *
     * @return string
     */
    public function createAgencyToken(ProxyRequestEvent $proxyRequestEvent)
    {
        $agencyToken = $this->targetCreateAgencyToken(
            $proxyRequestEvent->getTargetAgency()
        );

        $agencyData = $proxyRequestEvent->getTargetAgency();
        $agencyData->setTokensLeft($agencyToken['tokens_left']);

        $tokenData = new TargetAgencyToken();
        $tokenData->setAccessToken($agencyToken['access_token']);
        $tokenData->setRefreshToken($agencyToken['refresh_token']);
        $tokenData->setExpiredIn($agencyToken['expires_in']);
        $tokenData->setAgency($agencyData);

        $this->entityManager->persist($agencyData);
        $this->entityManager->persist($tokenData);
        $this->entityManager->flush();
        $proxyRequestEvent->setTargetAgency($proxyRequestEvent->getTargetAgency());
        $proxyRequestEvent->setTargetToken($agencyToken);
    }

    /**
     * @param ProxyRequestEvent $proxyRequestEvent
     * @param bool              $isNew
     */
    public function createClientToken(ProxyRequestEvent $proxyRequestEvent, $isNew = true)
    {
        $clientToken = $this->targetCreateClientToken(
            $proxyRequestEvent->getTargetAgency(),
            $proxyRequestEvent->getClientName()
        );

        $clientData = ($isNew) ? new TargetClient() : $proxyRequestEvent->getTargetClient();
        $clientData->setAgency($proxyRequestEvent->getTargetAgency());
        $clientData->setClientName($proxyRequestEvent->getClientName());
        $clientData->setTokensLeft($clientToken['tokens_left']);

        $tokenData = new TargetClientToken();
        $tokenData->setAccessToken($clientToken['access_token']);
        $tokenData->setRefreshToken($clientToken['refresh_token']);
        $tokenData->setExpiredIn($clientToken['expires_in']);
        $tokenData->setClient($clientData);

        $this->entityManager->persist($clientData);
        $this->entityManager->persist($tokenData);
        $this->entityManager->flush();
        $proxyRequestEvent->setTargetClient($clientData);
        $proxyRequestEvent->setTargetToken($clientToken);
    }

    /**
     * @param TargetClient $clientData
     *
     * @throws ServiceIsLocked
     *
     * @return string
     */
    public function updateExpiredAndReturnActualClientToken(TargetClient $clientData)
    {
        foreach ($clientData->getTokens() as $token) {
            if ((time() - $token->getLastUpdate()->getTimestamp()) > ($token->getExpiredIn() / 4)) {
                $refreshTokenData = $this->refreshToken($clientData->getAgency(), $token->getRefreshToken());

                $this->entityManager
                    ->getRepository(TargetClientToken::class)
                    ->updateClientToken(
                        $refreshTokenData['refresh_token'],
                        $refreshTokenData['access_token']
                    );

                return $refreshTokenData['access_token'];
            }

            return $token->getAccessToken();
        }

        throw new ServiceIsLocked(
            json_encode([
                'error' => 'Could not update tokens',
            ]),
            Response::HTTP_LOCKED
        );
    }

    /**
     * @param TargetAgency $agencyData
     *
     * @throws ServiceIsLocked
     *
     * @return string
     */
    public function updateExpiredAndReturnActualAgencyToken(TargetAgency $agencyData)
    {
        foreach ($agencyData->getTokens() as $token) {
            if (time() - $token->getLastUpdate()->getTimestamp() > ($token->getExpiredIn() / 48)) {
                $refreshTokenData = $this->refreshToken($agencyData, $token->getRefreshToken());

                $this->entityManager
                    ->getRepository(TargetAgencyToken::class)
                    ->updateAgencyToken(
                            $refreshTokenData['refresh_token'],
                            $refreshTokenData['access_token']
                        );

                return $refreshTokenData['access_token'];
            }

            return $token->getAccessToken();
        }

        throw new ServiceIsLocked(
            json_encode([
                'error' => 'Could not update tokens',
            ]),
            Response::HTTP_LOCKED
        );
    }

    /**
     * @param TargetAgency $agencyData
     * @param null|string  $clientName
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function targetDeleteClientToken(TargetAgency $agencyData, string $clientName = null)
    {
        return
            $this
                ->targetTokenApi
                ->tokenRequest(
                    [
                        'client_id' => $agencyData->getAgencyIndex(),
                        'client_secret' => $agencyData->getAgencySecret(),
                        'username' => $clientName,
                    ],
                    'deleteTokenUrl'
                );
    }

    /**
     * @param TargetAgency $agencyData
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function targetDeleteAgencyToken(TargetAgency $agencyData)
    {
        return
            $this
                ->targetTokenApi
                ->tokenRequest(
                    [
                        'client_id' => $agencyData->getAgencyIndex(),
                        'client_secret' => $agencyData->getAgencySecret(),
                    ],
                    'deleteTokenUrl'
                );
    }

    /**
     * @param TargetAgency $agencyData
     * @param null|string  $clientName
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function targetCreateClientToken(TargetAgency $agencyData, string $clientName = null)
    {
        return
            $this
                ->targetTokenApi
                ->tokenRequest(
                    [
                        'grant_type' => 'agency_client_credentials',
                        'client_id' => $agencyData->getAgencyIndex(),
                        'client_secret' => $agencyData->getAgencySecret(),
                        'agency_client_name' => $clientName,
                    ],
                    'getTokenUrl'
                );
    }

    /**
     * @param TargetAgency $agencyData
     *
     * @return string
     */
    public function targetCreateAgencyToken(TargetAgency $agencyData)
    {
        return
            $this
                ->targetTokenApi
                ->tokenRequest(
                    [
                        'grant_type' => 'client_credentials',
                        'client_id' => $agencyData->getAgencyIndex(),
                        'client_secret' => $agencyData->getAgencySecret(),
                    ],
                    'getTokenUrl'
                );
    }

    /**
     * @param TargetAgency $agencyData
     * @param null|string  $refreshToken
     *
     * @return string
     */
    public function refreshToken(TargetAgency $agencyData, string $refreshToken = null)
    {
        return
            $this->targetTokenApi->tokenRequest(
                [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refreshToken,
                    'client_id' => $agencyData->getAgencyIndex(),
                    'client_secret' => $agencyData->getAgencySecret(),
                ],
                'getTokenUrl'
            );
    }

    /**
     * @param $errorText
     * @param $errorMessages
     * @param ProxyRequestEvent $proxyRequestEvent
     *
     * @return bool
     */
    public function tokenLimitErrorHandler($errorText, $errorMessages, ProxyRequestEvent $proxyRequestEvent): bool
    {
        $targetError = new TargetError(\GuzzleHttp\json_decode($errorText, true), $errorMessages);

        if (in_array($targetError->getCode(), $errorMessages, true) ||
            in_array($targetError->getMessage(), $errorMessages, true)) {
            if ($proxyRequestEvent->getTargetClient()) {
                $this->dropAllClientTokens($proxyRequestEvent);
                $this->createClientToken($proxyRequestEvent, false);

                return true;
            }

            if ($proxyRequestEvent->getTargetAgency()) {
                $this->dropAllAgencyTokens($proxyRequestEvent);
                $this->createAgencyToken($proxyRequestEvent);

                return true;
            }
        }

        return false;
    }

    /**
     * @param ProxyRequestEvent $proxyRequestEvent
     *
     * @return bool
     */
    public function dropAllClientTokens(ProxyRequestEvent $proxyRequestEvent): bool
    {
        $this->targetDeleteClientToken(
            $proxyRequestEvent->getTargetAgency(),
            $proxyRequestEvent->getClientName()
        );

        if (0 !== $proxyRequestEvent->getTargetClient()->countTokens()) {
            foreach ($proxyRequestEvent->getTargetClient()->getTokens() as $clientToken) {
                $this->entityManager->remove($clientToken);
            }
            $this->entityManager->flush();
            $proxyRequestEvent->getTargetClient()->setTokensLeft(null);
        }

        return true;
    }

    /**
     * @param ProxyRequestEvent $proxyRequestEvent
     *
     * @return bool
     */
    public function dropAllAgencyTokens(ProxyRequestEvent $proxyRequestEvent): bool
    {
        $this->targetDeleteAgencyToken($proxyRequestEvent->getTargetAgency());
        if (0 !== $proxyRequestEvent->getTargetAgency()->countTokens()) {
            foreach ($proxyRequestEvent->getTargetAgency()->getTokens() as $agencyToken) {
                $this->entityManager->remove($agencyToken);
            }
            $this->entityManager->flush();
            $proxyRequestEvent->getTargetAgency()->setTokensLeft(null);
        }

        return true;
    }
}
