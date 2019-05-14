<?php

namespace TargetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TargetLogs..
 *
 * @ORM\Entity(repositoryClass="TargetBundle\Repository\TargetLogsRepository")
 * @ORM\Table(name="tm_logs")
 */
class TargetLogs
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
     * @ORM\Column(name="client_name", type="string")
     */
    private $clientName;

    /**
     * @var string
     *
     * @ORM\Column(name="request_path", type="string")
     */
    private $requestPath;

    /**
     * @var string
     *
     * @ORM\Column(name="request_status", type="string")
     */
    private $requestStatus;

    /**
     * @var string
     *
     * @ORM\Column(name="request_method", type="string")
     */
    private $requestMethod;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="request_datetime", type="datetime")
     */
    private $requestDatetime;

    /**
     * @var int
     *
     * @ORM\Column(name="request_per_day_remains", type="integer")
     */
    private $requestPerDayRemains;

    /**
     * @var int
     *
     * @ORM\Column(name="request_per_hour_remains", type="integer")
     */
    private $requestPerHourRemains;

    /**
     * @var int
     *
     * @ORM\Column(name="request_per_minute_remains", type="integer")
     */
    private $requestPerMinuteRemains;

    /**
     * @var int
     *
     * @ORM\Column(name="request_per_second_remains", type="integer")
     */
    private $requestPerSecondRemains;

    /**
     * TargetLogs constructor.
     */
    public function __construct()
    {
        $this->requestDatetime = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param string $clientName
     *
     * @return TargetLogs
     */
    public function setClientName(string $clientName = null): self
    {
        $this->clientName = $clientName;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientName(): ?string
    {
        return $this->clientName;
    }

    /**
     * @param string $requestPath
     *
     * @return TargetLogs
     */
    public function setRequestPath(string $requestPath = null): self
    {
        $this->requestPath = $requestPath;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getRequestPath(): ?string
    {
        return $this->requestPath;
    }

    /**
     * @param string $requestStatus
     *
     * @return TargetLogs
     */
    public function setRequestStatus(string $requestStatus = null): self
    {
        $this->requestStatus = $requestStatus;

        return $this;
    }

    /**
     * @return string
     */
    public function getRequestStatus(): ?string
    {
        return $this->requestStatus;
    }

    /**
     * @param string $requestMethod
     *
     * @return TargetLogs
     */
    public function setRequestMethod(string $requestMethod = null): self
    {
        $this->requestMethod = $requestMethod;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getRequestMethod(): ?string
    {
        return $this->requestMethod;
    }

    /**
     * @param \DateTime $requestDatetime
     *
     * @return TargetLogs
     */
    public function setRequestDatetime(\DateTime $requestDatetime): self
    {
        $this->requestDatetime = $requestDatetime;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getRequestDatetime(): \DateTime
    {
        return $this->requestDatetime;
    }

    /**
     * @param int $requestPerDayRemains
     *
     * @return TargetLogs
     */
    public function setRequestPerDayRemains(int $requestPerDayRemains = null): self
    {
        $this->requestPerDayRemains = $requestPerDayRemains;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getRequestPerDayRemains(): ?int
    {
        return $this->requestPerDayRemains;
    }

    /**
     * @param int $requestPerHourRemains
     *
     * @return TargetLogs
     */
    public function setRequestPerHourRemains(int $requestPerHourRemains = null): self
    {
        $this->requestPerHourRemains = $requestPerHourRemains;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getRequestPerHourRemains(): ?int
    {
        return $this->requestPerHourRemains;
    }

    /**
     * @param int $requestPerMinuteRemains
     *
     * @return TargetLogs
     */
    public function setRequestPerMinuteRemains(int $requestPerMinuteRemains = null): self
    {
        $this->requestPerMinuteRemains = $requestPerMinuteRemains;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getRequestPerMinuteRemains(): ?int
    {
        return $this->requestPerMinuteRemains;
    }

    /**
     * @param int $requestPerSecondRemains
     *
     * @return TargetLogs
     */
    public function setRequestPerSecondRemains(int $requestPerSecondRemains = null): self
    {
        $this->requestPerSecondRemains = $requestPerSecondRemains;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getRequestPerSecondRemains(): ?int
    {
        return $this->requestPerSecondRemains;
    }
}
