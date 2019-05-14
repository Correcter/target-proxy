<?php

namespace TargetBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * TargetAgency..
 *
 * @ORM\Entity(repositoryClass="TargetBundle\Repository\TargetAgencyRepository")
 * @ORM\Table(name="tm_agencies")
 */
class TargetAgency
{
    /**
     * @ORM\OneToMany(targetEntity="TargetBundle\Entity\TargetAgencyToken", mappedBy="agency", cascade={"persist"})
     */
    protected $tokens;

    /**
     * @ORM\OneToMany(targetEntity="TargetBundle\Entity\TargetClient", mappedBy="agency")
     */
    private $clients;

    /**
     * @ORM\ManyToMany(targetEntity="TargetBundle\Entity\TargetCompany", inversedBy="agencies", cascade={"persist"})
     * @ORM\JoinTable(name="tm_company_agencies",
     *      joinColumns={@ORM\JoinColumn(name="agency_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="company_id", referencedColumnName="id")}
     *      )
     */
    private $companies;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="agency_index", type="string")
     */
    private $agencyIndex;

    /**
     * @var string
     *
     * @ORM\Column(name="agency_name", type="string")
     */
    private $agencyName;

    /**
     * @var string
     *
     * @ORM\Column(name="tokens_left", type="string")
     */
    private $tokensLeft;

    /**
     * @var string
     *
     * @ORM\Column(name="agency_secret", type="string", nullable=true)
     */
    private $agencySecret;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_sandbox", type="boolean", nullable=false)
     */
    private $isSandbox;

    /**
     * TargetAgency constructor.
     */
    public function __construct()
    {
        $this->isSandbox = false;
        $this->companies = new ArrayCollection();
        $this->clients = new ArrayCollection();
        $this->tokens = new ArrayCollection();
    }

    /**
     * @param null|int $id
     */
    public function setId(int $id = null)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param string $agencyIndex
     *
     * @return TargetAgency
     */
    public function setAgencyIndex(string $agencyIndex): self
    {
        $this->agencyIndex = $agencyIndex;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getAgencyIndex(): ?string
    {
        return $this->agencyIndex;
    }

    /**
     * @param string $agencyName
     *
     * @return TargetAgency
     */
    public function setAgencyName(string $agencyName): self
    {
        $this->agencyName = $agencyName;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getAgencyName(): ?string
    {
        return $this->agencyName;
    }

    /**
     * @param int|null $tokensLeft
     * @return TargetAgency
     */
    public function setTokensLeft(int $tokensLeft = null): self
    {
        $this->tokensLeft = $tokensLeft;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getTokensLeft(): ?int
    {
        return $this->tokensLeft;
    }

    /**
     * @param string $agencySecret
     *
     * @return TargetAgency
     */
    public function setAgencySecret(string $agencySecret): self
    {
        $this->agencySecret = $agencySecret;

        return $this;
    }

    /**
     * @return string
     */
    public function getAgencySecret(): ?string
    {
        return $this->agencySecret;
    }

    /**
     * @param bool $sandbox
     *
     * @return TargetAgency
     */
    public function setSandbox(bool $sandbox = true): self
    {
        $this->isSandbox = $sandbox;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSandbox(): bool
    {
        return $this->isSandbox ? true : false;
    }

    /**
     * @param TargetAgencyToken $token
     *
     * @return TargetAgency
     */
    public function addToken(TargetAgencyToken $token): self
    {
        $token->setAgency($this);

        if (!$this->tokens->contains($token)) {
            $this->tokens[] = $token;
        }

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
     * @param TargetAgencyToken
     */
    public function removeToken(TargetAgencyToken $token)
    {
        if (!$this->tokens->contains($token)) {
            $this->tokens->removeElement($token);
        }
    }

    /**
     * @param TargetClient $client
     *
     * @return TargetAgency
     */
    public function addClient(TargetClient $client): self
    {
        $client->setAgency($this);

        if (!$this->clients->contains($client)) {
            $this->clients[] = $client;
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }

    /**
     * @param TargetClient $client
     *
     * @return TargetAgency
     */
    public function removeClient(TargetClient $client): self
    {
        if (!$this->clients->contains($client)) {
            $this->clients->removeElement($client);
        }

        return $this;
    }

    /**
     * @param TargetCompany $company
     *
     * @return TargetAgency
     */
    public function addCompany(TargetCompany $company): self
    {
        $company->addAgency($this);

        if (!$this->companies->contains($company)) {
            $this->companies[] = $company;
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getCompanies(): Collection
    {
        return $this->companies;
    }

    /**
     * @param TargetCompany $company
     *
     * @return TargetAgency
     */
    public function removeCompany(TargetCompany $company): self
    {
        if ($this->companies->contains($company)) {
            $this->companies->removeElement($company);
        }

        return $this;
    }
}
