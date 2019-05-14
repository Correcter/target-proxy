<?php

namespace TargetBundle\EventListener;

use Doctrine\ORM\EntityManager;
use ProxyBundle\Event\ProxyRequestEvent;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use TargetBundle\Entity\TargetAgency;
use TargetBundle\Entity\TargetClient;
use TargetBundle\Exceptions\TargetException;
use TargetBundle\Service\TargetTokenManager;

/**
 * @author Vitaly Dergunov
 */
class ProxyCheckerListener extends AbstractListener
{
    /**
     * @var ParameterBag
     */
    private $tokenParams;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var TargetTokenManager
     */
    private $tokenManager;

    /**
     * ProxyCheckerListener constructor.
     *
     * @param ParameterBag       $tokenParams
     * @param EntityManager      $entityManager
     * @param TokenStorage       $tokenStorage
     * @param TargetTokenManager $tokenManager
     */
    public function __construct(
        ParameterBag $tokenParams,
        EntityManager $entityManager,
        TokenStorage $tokenStorage,
        TargetTokenManager $tokenManager
    ) {
        parent::__construct($entityManager);

        $this->tokenParams = $tokenParams;
        $this->tokenStorage = $tokenStorage;
        $this->tokenManager = $tokenManager;
    }

    /**
     * @param ProxyRequestEvent $proxyRequestEvent
     *
     * @return bool
     */
    public function onCheckUri(ProxyRequestEvent $proxyRequestEvent): bool
    {
        if ('/' === $proxyRequestEvent->getRequest()->pathInfo()) {
            $proxyRequestEvent->setResponse(
                new JsonResponse([
                    'error' => 'Method is not defined!',
                ], Response::HTTP_FORBIDDEN)
            );

            return false;
        }

        if (1 === preg_match('/(\/{2,6})/', $proxyRequestEvent->getRequest()->pathInfo())) {
            $proxyRequestEvent->setResponse(
                new JsonResponse([
                    'error' => 'Invalid URI!',
                ], Response::HTTP_FORBIDDEN)
            );

            return false;
        }

        return true;
    }

    /**
     * @param ProxyRequestEvent $proxyRequestEvent
     *
     * @return bool
     */
    public function onCheckProxyType(ProxyRequestEvent $proxyRequestEvent): bool
    {
        if ('target' !== $proxyRequestEvent->getProxyType()) {
            $proxyRequestEvent->setResponse(
                new JsonResponse([
                    'error' => 'Unsupported proxy service request!',
                ], Response::HTTP_LOCKED)
            );

            return false;
        }

        return true;
    }

    /**
     * @param ProxyRequestEvent $proxyRequestEvent
     *
     * @return bool
     */
    public function onAgencyAndClientIsReceived(ProxyRequestEvent $proxyRequestEvent): bool
    {
        $accessData = $proxyRequestEvent->getRequest()->common();

        $proxyRequestEvent->setClientName(
            $accessData['get']['client'] ??
            $accessData['post']['client'] ??
            $accessData['headers']['x-target-client'][0] ?? false
        );

        $proxyRequestEvent->setAgencyName(
            $accessData['get']['agency'] ??
            $accessData['post']['agency'] ??
            $accessData['headers']['x-target-agency'][0] ?? false
        );

        if (!$proxyRequestEvent->getAgencyName()) {
            $proxyRequestEvent->setResponse(
                new JsonResponse([
                    'error' => 'The agency is not received!',
                ], Response::HTTP_FORBIDDEN)
            );

            return false;
        }

        return true;
    }

    /**
     * @param ProxyRequestEvent $proxyRequestEvent
     *
     * @return bool
     */
    public function onSetupCredentials(ProxyRequestEvent $proxyRequestEvent): bool
    {
        $agencyData = $this->entityManager
            ->getRepository(TargetAgency::class)
            ->getAgencyByName($proxyRequestEvent->getAgencyName());

        if (!$agencyData) {
            $proxyRequestEvent->setResponse(
                new JsonResponse([
                    'error' => 'Agency client is invalid',
                ], Response::HTTP_FORBIDDEN)
            );

            return false;
        }

        $proxyRequestEvent->setTargetAgency($agencyData);

        $clientData =
            $this->entityManager
                ->getRepository(TargetClient::class)
                ->getClientByName($proxyRequestEvent->getClientName());

        if ($clientData) {
            $proxyRequestEvent->setTargetClient($clientData);
        }

        return true;
    }

    /**
     * @param ProxyRequestEvent $proxyRequestEvent
     *
     * @return bool
     */
    public function onCreateUserIfNotExists(ProxyRequestEvent $proxyRequestEvent): bool
    {
        try {
            $clientData = $proxyRequestEvent->getTargetClient();
            $agencyData = $proxyRequestEvent->getTargetAgency();

            if ($proxyRequestEvent->getClientName() && !$clientData) {
                $this->tokenManager->createClientToken($proxyRequestEvent, true);

                return true;
            }

            if ($clientData && ($clientData->countTokens() < $this->tokenParams->get('maxTokensCount'))) {
                $this->tokenManager->createClientToken($proxyRequestEvent, false);

                return true;
            }

            if (!$proxyRequestEvent->getClientName() &&
                ($agencyData->countTokens() < $this->tokenParams->get('maxTokensCount'))) {
                $this->tokenManager->createAgencyToken($proxyRequestEvent);

                return true;
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
            return true;
        }
    }

    /**
     * @param ProxyRequestEvent $proxyRequestEvent
     *
     * @return bool
     */
    public function onCheckToken(ProxyRequestEvent $proxyRequestEvent): bool
    {
        if (null === $this->tokenStorage->getToken()) {
            $proxyRequestEvent->setResponse(
                new JsonResponse([
                    'error' => 'Failed to identify the client\'s token!',
                ], Response::HTTP_FORBIDDEN)
            );

            return false;
        }

        return true;
    }

    /**
     * @param ProxyRequestEvent $proxyRequestEvent
     *
     * @return bool
     */
    public function onCheckAccessMethods(ProxyRequestEvent $proxyRequestEvent): bool
    {
        $allowedMethods = $this->entityManager
            ->getRepository(\TargetBundle\Entity\TargetMethod::class)
            ->getMethodsByCompany(
                $this->tokenStorage->getToken()->getUsername()
            );

        foreach ($allowedMethods as $method) {
            $method = str_replace('/', '\/', $method['methodName']);

            if ('*' === $method) {
                return true;
            }

            if (strpos($method, '*') > 0) {
                $method = str_replace('*', '.+', $method);
            }

            if (preg_match("/${method}\\.json$/i", addslashes($proxyRequestEvent->getRequest()->pathInfo()))) {
                return true;
            }
        }

        $proxyRequestEvent->setResponse(
            new JsonResponse([
                'error' => 'Method is not allowed!',
            ], Response::HTTP_FORBIDDEN)
        );

        return false;
    }

    /**
     * @param ProxyRequestEvent $proxyRequestEvent
     *
     * @return bool
     */
    public function onCheckHttpMethod(ProxyRequestEvent $proxyRequestEvent): bool
    {
        $allowedHttpMethods = $this->entityManager
            ->getRepository(\TargetBundle\Entity\TargetHttpMethod::class)
            ->getHttpMethodsByCompany(
                $this->tokenStorage->getToken()->getUsername()
            );

        foreach ($allowedHttpMethods as $httpMethod) {
            $httpMethod = mb_strtoupper(addslashes($httpMethod['httpMethodName']), 'utf-8');

            if ('*' === $httpMethod) {
                return true;
            }

            if ($httpMethod === $proxyRequestEvent->getRequest()->method()) {
                return true;
            }
        }

        $proxyRequestEvent->setResponse(
            new JsonResponse([
                'error' => 'HTTP method is not allowed!',
            ], Response::HTTP_FORBIDDEN)
        );

        return false;
    }

    /**
     * @param ProxyRequestEvent $proxyRequestEvent
     *
     * @return bool
     */
    public function onCheckCompany(ProxyRequestEvent $proxyRequestEvent)
    {
        if ($proxyRequestEvent->getTargetAgency()) {
            foreach ($proxyRequestEvent->getTargetAgency()->getCompanies() as $company) {
                if ($this->tokenStorage->getToken()->getUsername() === $company->getCompanyName()) {
                    return true;
                }
            }
        }

        $proxyRequestEvent->setResponse(
            new JsonResponse([
                'error' => 'Unknown company!',
            ], Response::HTTP_FORBIDDEN)
        );

        return false;
    }
}
