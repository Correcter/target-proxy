<?php

namespace TargetBundle\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use ProxyBundle\DTO\RequestDTO;
use Symfony\Component\HttpFoundation\Response;
use TargetBundle\Exceptions\IncompleteRequest;
use TargetBundle\Exceptions\TargetException;
use TargetBundle\Exceptions\TargetNotFound;

/**
 * @author Vitaly Dergunov
 */
class TargetClientApi
{
    /**
     * @var string
     */
    private $clientHost;

    /**
     * TargetClientApi constructor.
     *
     * @param string $clientHost
     */
    public function __construct(string $clientHost)
    {
        $this->clientHost = $clientHost;
    }

    /**
     * @param RequestDTO $targetClientRequest
     * @param string     $token
     *
     * @throws IncompleteRequest
     * @throws TargetException
     * @throws TargetNotFound
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function request(RequestDTO $targetClientRequest, string $token)
    {
        $targetClientRequest->setUpHost($this->clientHost);

        $clientRequest = $targetClientRequest->common();

        try {
            $client = new Client(['base_uri' => $this->clientHost]);

            $basicRequest = [
                'headers' => [
                    'Authorization' => 'Bearer '.$token,
                    'Accept-Encoding' => 'gzip, deflate',
                ],
                'query' => $clientRequest['get'],
                'body' => $clientRequest['content'],
                'connect_timeout' => 36000,
                'verify' => false,
            ];

            return
                $client->request(
                    $targetClientRequest->method(),
                    $this->clientHost.$targetClientRequest->pathInfo(),
                    $basicRequest
                );
        } catch (TransferException $exc) {
            if (!$exc->hasResponse()) {
                throw new TargetNotFound(json_encode([
                    'error' => 'The service is not responding',
                ]), Response::HTTP_BAD_REQUEST);
            }

            if (Response::HTTP_NOT_FOUND === $exc->getCode()) {
                throw new TargetException(json_encode([
                    'error' => 'Invalid request path or method',
                ]), Response::HTTP_NOT_FOUND);
            }

            throw new TargetException($exc->getResponse()->getBody(true)->getContents(), Response::HTTP_BAD_REQUEST);
        }
    }
}
