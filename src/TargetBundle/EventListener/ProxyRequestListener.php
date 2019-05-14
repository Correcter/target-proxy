<?php

namespace TargetBundle\EventListener;

use Doctrine\ORM\EntityManager;
use ProxyBundle\Event\ProxyRequestEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use TargetBundle\Exceptions\TargetException;
use TargetBundle\Service\TargetClientApi;
use TargetBundle\Service\TargetLogManager;
use TargetBundle\Service\TargetTokenManager;

/**
 * @author Vitaly Dergunov
 */
class ProxyRequestListener extends AbstractListener
{
    /**
     * @var TargetClientApi
     */
    private $targetClientApi;

    /**
     * @var TargetTokenManager
     */
    private $tokenManager;

    /**
     * @var TargetLogManager
     */
    private $logManager;

    /**
     * ProxyRequestListener constructor.
     *
     * @param TargetClientApi    $targetClientApi
     * @param EntityManager      $entityManager
     * @param TargetTokenManager $tokenManager
     * @param TargetLogManager   $logManager
     */
    public function __construct(
        TargetClientApi $targetClientApi,
        EntityManager $entityManager,
        TargetTokenManager $tokenManager,
        TargetLogManager $logManager
    ) {
        $this->targetClientApi = $targetClientApi;
        $this->tokenManager = $tokenManager;
        $this->logManager = $logManager;

        parent::__construct($entityManager);
    }

    /**
     * @param ProxyRequestEvent $proxyRequestEvent
     *
     * @return bool
     */
    public function onProxyRequest(ProxyRequestEvent $proxyRequestEvent)
    {
        $proxyRequestEvent->stopPropagation();

        try {
            $token = $this->tokenManager->getTargetToken($proxyRequestEvent);

            if (null !== $token) {
                $this->run($proxyRequestEvent, $token);
            }
        } catch (\Doctrine\ORM\NoResultException $exc) {
            $proxyRequestEvent->setResponse(
                new JsonResponse([
                    'error' => $exc->getMessage(),
                ], Response::HTTP_NOT_FOUND)
            );
        } catch (\Doctrine\DBAL\DBALException $exc) {
            $proxyRequestEvent->setResponse(
                new JsonResponse([
                    'error' => $exc->getMessage(),
                ], Response::HTTP_BAD_REQUEST)
            );
        } catch (TargetException $exc) {
            if ($this->tokenManager->tokenLimitErrorHandler(
                $exc->getMessage(),
                [
                    'token_limit_exceeded',
                    'invalid_token',
                ],
                $proxyRequestEvent
            )
            ) {
                $this->onProxyRequest($proxyRequestEvent);

                return false;
            }

            $proxyRequestEvent->setResponse(
                new Response(
                    $exc->getMessage(),
                    Response::HTTP_BAD_REQUEST,
                    [
                        'content-type' => 'application/json',
                    ]
                )
            );
        } finally {
            $this->logManager->saveLogs($proxyRequestEvent);
        }
    }

    /**
     * @param ProxyRequestEvent $proxyRequestEvent
     * @param $token
     */
    public function run(ProxyRequestEvent $proxyRequestEvent, $token)
    {
        $unwrapTargetResponse = $this->targetClientApi->request(
            $proxyRequestEvent->getRequest(),
            $token
        );

        $proxyRequestEvent->setHeaders(
            $unwrapTargetResponse->getHeaders()
        );

        $proxyRequestEvent->setResponse(
            new StreamedResponse(
                function () use ($unwrapTargetResponse) {
                    echo $unwrapTargetResponse->getBody()->getContents();
                },
                    $unwrapTargetResponse->getStatusCode(),
                [
                    'content-type' => 'application/json',
                ]
            )
        );
    }
}
