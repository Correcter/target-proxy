<?php

namespace TargetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TargetAgencyToken..
 *
 * @ORM\Entity(repositoryClass="TargetBundle\Repository\TargetAgencyTokenRepository")
 * @ORM\Table(name="tm_agency_tokens")
 */
class TargetAgencyToken
{
    /**
     * @ORM\ManyToOne(targetEntity="TargetBundle\Entity\TargetAgency", inversedBy="tokens", cascade={"persist"})
     * @ORM\JoinColumn(name="agency_id", referencedColumnName="id")
     */
    private $agency;

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
     * @ORM\Column(name="agency_id", type="integer")
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
     * @param int $agencyId
     *
     * @return TargetAgencyToken
     */
    public function setAgencyId(int $agencyId): self
    {
        $this->agencyId = $agencyId;

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
     * @return TargetAgencyToken
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
     * @param TargetAgency $agency
     *
     * @return TargetAgencyToken
     */
    public function setAgency(TargetAgency $agency = null): self
    {
        $this->agency = $agency;

        return $this;
    }

    /**
     * @return TargetAgency
     */
    public function getAgency(): ?TargetAgency
    {
        return $this->agency;
    }

    /**
     * @param null|string $accessToken
     *
     * @return TargetAgencyToken
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
     * @return TargetAgencyToken
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
