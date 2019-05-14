<?php

namespace TargetBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * TargetCompany..
 *
 * @ORM\Entity(repositoryClass="TargetBundle\Repository\TargetCompanyRepository")
 * @ORM\Table(name="tm_companies")
 */
class TargetCompany
{
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
     * @ORM\Column(name="company_name", type="string")
     */
    private $companyName;

    /**
     * @ORM\ManyToMany(targetEntity="TargetBundle\Entity\TargetAgency", mappedBy="companies" , cascade={"persist"})
     */
    private $agencies;

    /**
     * @ORM\ManyToMany(targetEntity="TargetBundle\Entity\TargetMethod", mappedBy="companies", cascade={"persist"})
     */
    private $methods;

    /**
     * @ORM\ManyToMany(targetEntity="TargetBundle\Entity\TargetHttpMethod", mappedBy="companies", cascade={"persist"})
     */
    private $httpMethods;

    /**
     * TargetCompany constructor.
     */
    public function __construct()
    {
        $this->agencies = new ArrayCollection();
        $this->methods = new ArrayCollection();
        $this->httpMethods = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param string $companyName
     *
     * @return TargetCompany
     */
    public function setCompanyName(string $companyName): self
    {
        $this->companyName = $companyName;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * @param TargetAgency $agency
     *
     * @return TargetCompany
     */
    public function addAgency(TargetAgency $agency): self
    {
        $agency->addCompany($this);

        if (!$this->agencies->contains($agency)) {
            $this->agencies[] = $agency;
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getAgencies(): Collection
    {
        return $this->agencies;
    }

    /**
     * @param TargetMethod $method
     *
     * @return TargetCompany
     */
    public function addMethod(TargetMethod $method): self
    {
        $method->addCompany($this);

        if (!$this->methods->contains($method)) {
            $this->methods[] = $method;
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getMethods(): Collection
    {
        return $this->methods;
    }

    /**
     * @param TargetHttpMethod $httpMethod
     *
     * @return TargetCompany
     */
    public function addHttpMethod(TargetHttpMethod $httpMethod): self
    {
        $httpMethod->addCompany($this);

        if (!$this->httpMethods->contains($httpMethod)) {
            $this->httpMethods[] = $httpMethod;
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getHttpMethods(): Collection
    {
        return $this->httpMethods;
    }

    /**
     * @param TargetAgency $agency
     *
     * @return TargetAgency
     */
    public function removeAgency(TargetAgency $agency): self
    {
        if (!$this->agencies->contains($agency)) {
            $this->agencies->removeElement($agency);
        }

        return $this;
    }

    /**
     * @param TargetMethod $method
     *
     * @return TargetCompany
     */
    public function removeMethod(TargetMethod $method)
    {
        if (!$this->methods->contains($method)) {
            $this->methods->removeElement($method);
        }

        return $this;
    }

    /**
     * @param TargetHttpMethod $httpMethod
     *
     * @return TargetCompany
     */
    public function removeHttpMethod(TargetHttpMethod $httpMethod)
    {
        if (!$this->httpMethods->contains($httpMethod)) {
            $this->httpMethods->removeElement($httpMethod);
        }

        return $this;
    }
}
