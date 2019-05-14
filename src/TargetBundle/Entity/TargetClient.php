<?php

namespace TargetBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * TargetClient...
 *
 * @ORM\Entity(repositoryClass="TargetBundle\Repository\TargetClientRepository")
 * @ORM\Table(name="tm_clients")
 */
class TargetClient
{
    /**
     * @ORM\OneToMany(targetEntity="TargetBundle\Entity\TargetClientToken", mappedBy="client", cascade={"persist"})
     */
    protected $tokens;

    /**
     * @ORM\ManyToOne(targetEntity="TargetBundle\Entity\TargetAgency", inversedBy="clients")
     * @ORM\JoinColumn(name="agency_id", referencedColumnName="id")
     */
    protected $agency;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="agency_id", type="integer")
     */
    private $agencyId;

    /**
     * @var string
     *
     * @ORM\Column(name="client_name", type="string")
     */
    private $clientName;

    /**
     * @var string
     *
     * @ORM\Column(name="tokens_left", type="string")
     */
    private $tokensLeft;

    /**
     * TargetClient constructor.
     */
    public function __construct()
    {
        $this->tokens = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $agencyId
     *
     * @return TargetClient
     */
    public function setAgencyId(int $agencyId = null): self
    {
        $this->agencyId = $agencyId;

        return $this;
    }

    /**
     * @return int
     */
    public function getAgencyId(): ?int
    {
        return $this->agencyId;
    }

    /**
     * @param string $clientName
     *
     * @return TargetClient
     */
    public function setClientName(string $clientName = null): self
    {
        $this->clientName = $clientName;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getClientName(): ?string
    {
        return $this->clientName;
    }

    /**
     * @param int $tokensLeft
     *
     * @return TargetClient
     */
    public function setTokensLeft(int $tokensLeft = null): self
    {
        $this->tokensLeft = $tokensLeft;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getTokensLeft(): ?int
    {
        return $this->tokensLeft;
    }

    /**
     * @param TargetAgency $agency
     *
     * @return TargetClient
     */
    public function setAgency(TargetAgency $agency): self
    {
        $this->agency = $agency;

        return $this;
    }

    /**
     * @return null|TargetAgency
     */
    public function getAgency(): ?TargetAgency
    {
        return $this->agency;
    }

    /**
     * @param TargetClientToken $token
     *
     * @return TargetClient
     */
    public function addToken(TargetClientToken $token): self
    {
        $token->setClient($this);

        if (!$this->tokens->contains($token)) {
            $this->tokens[] = $token;
        }

        return $this;
    }

    /**
     * @return TargetClient
     */
    public function zeroizeTokens(): self
    {
        $this->tokens = new ArrayCollection();

        return $this;
    }

    /**
     * @return int
     */
    public function countTokens(): int
    {
        return $this->tokens->count();
    }

    /**
     * @return Collection
     */
    public function getTokens(): Collection
    {
        return $this->tokens;
    }

    /**
     * @param TargetClientToken
     */
    public function removeToken(TargetClientToken $token)
    {
        if (!$this->tokens->contains($token)) {
            $this->tokens->removeElement($token);
        }
    }
}
