<?php

namespace TargetBundle\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Response;
use TargetBundle\Exceptions\TargetException;

/**
 * @author Vitaly Dergunov
 */
class TargetTokenApi
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ParameterBag
     */
    private $parameters;

    /**
     * TargetTokenApi constructor.
     *
     * @param ParameterBag    $parameters
     * @param LoggerInterface $logger
     */
    public function __construct(
        ParameterBag $parameters,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->parameters = $parameters;
    }

    /**
     * @param array $clientData
     * @param null  $operationType
     *
     * @throws TargetException
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function tokenRequest(array $clientData = [], $operationType = null)
    {
        try {
            $targetMailData = (new Client([
                'base_uri' => $this->parameters->get('clientHost'),
            ]))->post(
            $this->parameters->get('clientHost').$this->parameters->get($operationType),
                [
                    'form_params' => $clientData,
                    'connect_timeout' => 300,
                    'verify' => false,
                ]
            );

            $targetMailData = json_decode($targetMailData->getBody()->getContents(), true);

            if (!isset($targetMailData['access_token']) && 'deleteTokenUrl' !== $operationType) {
                throw new TargetException(json_encode([
                    'error' => 'Wrong token request',
                ]), Response::HTTP_BAD_REQUEST);
            }

            return $targetMailData;
        } catch (TransferException $exc) {
            if (!$exc->hasResponse()) {
                throw new TargetException(json_encode([
                    'error' => 'We have not received a response',
                ]), Response::HTTP_NO_CONTENT);
            }

            throw new TargetException($exc->getResponse()->getBody(true)->getContents(), Response::HTTP_BAD_REQUEST);
        }
    }
}
