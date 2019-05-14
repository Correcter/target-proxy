<?php

namespace TargetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TargetClientToken.
 *
 * @ORM\Entity(repositoryClass="TargetBundle\Repository\TargetClientTokenRepository")
 * @ORM\Table(name="tm_client_tokens")
 */
class TargetClientToken
{
    /**
     * @ORM\ManyToOne(targetEntity="TargetBundle\Entity\TargetClient", inversedBy="tokens", cascade={"persist"})
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id")
     */
    private $client;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Id()
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="client_id", type="integer")
     */
    private $clientId;

    /**
     * @var string
     *
     * @ORM\Column(name="access_token", type="string", nullable=true)
     */
    private $accessToken;

    /**
     * @var int
     *
     * @ORM\Column(name="expired_in", type="integer")
     */
    private $expiredIn;

    /**
     * @var string
     *
     * @ORM\Column(name="refresh_token", type="string", nullable=true)
     */
    private $refreshToken;

    /**
     * @var \DateTime
     * @ORM\Column(name="last_update", type="datetime")
     */
    private $lastUpdate;

    /**
     * TargetClientToken constructor.
     *
     * @param string $accessToken
     * @param string $refreshToken
     * @param int    $clientId
     * @param int    $expiredIn
     */
    public function __construct(string $accessToken = null, string $refreshToken = null, int $clientId = null, int $expiredIn = null)
    {
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->clientId = $clientId;
        $this->expiredIn = $expiredIn;
        $this->lastUpdate = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $clientId
     *
     * @return TargetClientToken
     */
    public function setClientId(int $clientId): self
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * @return int
     */
    public function getClientId(): int
    {
        return $this->clientId;
    }

    /**
     * @param null|int $expiredIn
     *
     * @return TargetClientToken
     */
    public function setExpiredIn(int $expiredIn = null): self
    {
        $this->expiredIn = $expiredIn;

        return $this;
    }

    /**
     * @return int
     */
    public function getExpiredIn(): ?int
    {
        return $this->expiredIn;
    }

    /**
     * @param null|TargetClient $client
     *
     * @return TargetClientToken
     */
    public function setClient(TargetClient $client = null): self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return TargetClient
     */
    public function getClient(): ?TargetClient
    {
        return $this->client;
    }

    /**
     * @param null|string $accessToken
     *
     * @return TargetClientToken
     */
    public function setAccessToken(string $accessToken = null): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    /**
     * @return \DateTime
     */
    public function getLastUpdate(): \DateTime
    {
        return $this->lastUpdate;
    }

    /**
     * @param null|string $refreshToken
     *
     * @return TargetClientToken
     */
    public function setRefreshToken(string $refreshToken = null): self
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    /**
     * @return string
     */
    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }
}
