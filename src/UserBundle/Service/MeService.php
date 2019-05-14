<?php

namespace UserBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use TargetBundle\Entity\TargetClient;

/**
 * @author Vitaly Dergunov
 */
class MeService
{
    /**
     * @var string
     */
    private $proxyToken;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var MeService
     */
    private $entityManager;

    /**
     * MeService constructor.
     *
     * @param string        $proxyToken
     * @param TokenStorage  $tokenStorage
     * @param EntityManager $entityManager
     */
    public function __construct(
        string $proxyToken,
        TokenStorage $tokenStorage,
        EntityManager $entityManager
    ) {
        $this->proxyToken = $proxyToken;
        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
    }

    /**
     * @param Request $request
     *
     * @return null|string
     */
    public function getClientToken(Request $request): ?string
    {
        if (
            $this->checkToken($request) &&
            $this->clientIsReceived($request) &&
            $this->checkAccessMethods($request) &&
            $this->checkHttpMethod($request) &&
            $this->checkCompany($request)
        ) {
            // f45e88bf67bac264ab7a70892069feee
            $clientData =
                $this->entityManager
                    ->getRepository(\TargetBundle\Entity\TargetClient::class)
                    ->getTokensByClientName(
                        $request->get('client_name')
                    );

            if (!$clientData) {
                throw new \RuntimeException('Client has no tokens!');
            }

            foreach ($clientData as $client) {
                if ($client instanceof TargetClient) {
                    foreach ($client->getTokens() as $token) {
                        return $token->getAccessToken();
                    }
                }
            }
        }

        return null;
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    private function checkToken(Request $request): bool
    {
        if ($this->proxyToken !== $request->get('token')) {
            throw new \RuntimeException('Invalid token!');
        }

        return true;
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    private function clientIsReceived(Request $request): bool
    {
        if (!$request->get('client_name')) {
            throw new \RuntimeException('Client is required!');
        }

        return true;
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    private function checkAccessMethods(Request $request): bool
    {
        $allowedMethods = $this->entityManager
            ->getRepository(\TargetBundle\Entity\TargetMethod::class)
            ->getMethodsByCompany(
                $this->tokenStorage->getToken()->getUsername()
            );

        foreach ($allowedMethods as $method) {
            $method = $method['methodName'];

            if ('*' === $method) {
                return true;
            }

            if (strpos($method, '*') > 0) {
                $method = str_replace('*', '.+', $method);
            }

            if (str_replace('/', '', $request->getPathInfo()) === str_replace('/', '', $method)) {
                return true;
            }
        }

        throw new \RuntimeException('Unavailable request method!');
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    private function checkHttpMethod(Request $request): bool
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

            if ($httpMethod === $request->getMethod()) {
                return true;
            }
        }

        throw new \RuntimeException('Unavailable HTTP method!');
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    private function checkCompany(Request $request): bool
    {
        $clientCompany =
            $this->entityManager
                ->getRepository(\TargetBundle\Entity\TargetClient::class)
                ->getAgencyCompanyByClientName(
                    $request->get('client_name')
                );

        foreach ($clientCompany as $client) {
            foreach ($client->getAgency()->getCompanies() as $company) {
                if ($this->tokenStorage->getToken()->getUsername() === $company->getCompanyName()) {
                    return true;
                }
            }
        }

        throw new \RuntimeException('Incorrect request!');
    }
}
