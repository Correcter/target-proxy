<?php

namespace TargetBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * TargetMethod..
 *
 * @ORM\Entity(repositoryClass="TargetBundle\Repository\TargetMethodRepository")
 * @ORM\Table(name="tm_methods")
 */
class TargetMethod
{
    /**
     * @ORM\ManyToMany(targetEntity="TargetBundle\Entity\TargetCompany", inversedBy="methods", cascade={"persist"})
     * @ORM\JoinTable(name="tm_company_methods",
     *      joinColumns={@ORM\JoinColumn(name="method_id", referencedColumnName="id")},
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
     * @ORM\Column(name="method_name", type="string")
     */
    private $methodName;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled;

    /**
     * TargetCompany constructor.
     */
    public function __construct()
    {
        $this->companies = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param string $methodName
     *
     * @return TargetMethod
     */
    public function setMethodName(string $methodName): self
    {
        $this->methodName = $methodName;

        return $this;
    }

    /**
     * @return string
     */
    public function getMethodName(): ?string
    {
        return $this->methodName;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled = true)
    {
        $this->enabled = $enabled;
    }

    /**
     * @return bool
     */
    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    /**
     * @param TargetCompany $company
     *
     * @return TargetMethod
     */
    public function addCompany(TargetCompany $company): self
    {
        $company->addMethod($this);

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
     * @param TargetCompany
     */
    public function removeCompany(TargetCompany $company)
    {
        if (!$this->companies->contains($company)) {
            $this->companies->removeElement($company);
        }
    }
}
