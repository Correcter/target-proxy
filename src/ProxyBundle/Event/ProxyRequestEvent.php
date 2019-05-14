<?php

namespace ProxyBundle\Event;

use ProxyBundle\DTO\RequestDTO;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;
use TargetBundle\Entity\TargetAgency;
use TargetBundle\Entity\TargetClient;
use TargetBundle\Model\TargetToken;

/**
 * @author Vitaly Dergunov
 */
class ProxyRequestEvent extends Event
{
    /**
     * @var array
     */
    private $response;
    /**
     * @var RequestDTO
     */
    private $request;
    /**
     * @var string
     */
    private $proxyType;

    /**
     * @var string
     */
    private $clientName;

    /**
     * @var string
     */
    private $agencyName;

    /**
     * @var string
     */
    private $requestStatus;

    /**
     * @var array
     */
    private $responseHeaders;

    /**
     * @var TargetClient
     */
    private $targetClient;

    /**
     * @var TargetAgency
     */
    private $targetAgency;

    /**
     * @var null|TargetToken
     */
    private $targetToken;

    /**
     * ProxyTargetRequest constructor.
     *
     * @param RequestDTO $requestDTO
     * @param string     $proxyType
     */
    public function __construct(RequestDTO $requestDTO, $proxyType)
    {
        $this->request = $requestDTO;
        $this->proxyType = $proxyType;
    }

    /**
     * @return array
     */
    public function getHeaders(): ?array
    {
        return $this->responseHeaders;
    }

    /**
     * @param array $responseHeaders
     *
     * @return ProxyRequestEvent
     */
    public function setHeaders(array $responseHeaders = []): self
    {
        $this->responseHeaders = $responseHeaders;

        return $this;
    }

    /**
     * @return RequestDTO
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return bool
     */
    public function hasResponse()
    {
        return ($this->response) ? true : false;
    }

    /**
     * @return bool
     */
    public function responseIsJson()
    {
        json_decode($this->response);

        return JSON_ERROR_NONE === json_last_error();
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return string
     */
    public function getProxyType()
    {
        return $this->proxyType;
    }

    /**
     * @param Response $response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    /**
     * @return string
     */
    public function getClientName(): ?string
    {
        return $this->clientName;
    }

    /**
     * @param null|string $clientName
     */
    public function setClientName(string $clientName = null)
    {
        $this->clientName = $clientName;
    }

    /**
     * @return null|string
     */
    public function getAgencyName(): ?string
    {
        return $this->agencyName;
    }

    /**
     * @param null|string $agencyName
     */
    public function setAgencyName(string $agencyName = null)
    {
        $this->agencyName = $agencyName;
    }

    /**
     * @param null|TargetClient $targetClient
     */
    public function setTargetClient(TargetClient $targetClient)
    {
        $this->targetClient = $targetClient;
    }

    /**
     * @return TargetClient
     */
    public function getTargetClient(): ?TargetClient
    {
        return $this->targetClient;
    }

    /**
     * @param null|TargetAgency $targetAgency
     */
    public function setTargetAgency(TargetAgency $targetAgency)
    {
        $this->targetAgency = $targetAgency;
    }

    /**
     * @return TargetAgency
     */
    public function getTargetAgency(): ?TargetAgency
    {
        return $this->targetAgency;
    }

    /**
     * @param array $targetToken
     */
    public function setTargetToken(array $targetToken = [])
    {
        $this->targetToken = new TargetToken($targetToken);
    }

    /**
     * @return null|TargetToken
     */
    public function getTargetToken(): ?TargetToken
    {
        return $this->targetToken;
    }
}
