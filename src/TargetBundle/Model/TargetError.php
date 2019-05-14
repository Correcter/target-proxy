<?php

namespace TargetBundle\Model;

/**
 * Class TargetError.
 */
class TargetError
{
    /**
     * @var null|string
     */
    private $code;

    /**
     * @var null|string
     */
    private $message;

    /**
     * TargetError constructor.
     *
     * @param array $errorData
     * @param array $errorDescription
     */
    public function __construct(array $errorData = [], array $errorDescription = [])
    {
        $errorData = $errorData['error'] ?? $errorData;

        if (is_string($errorData)) {
            $this->message = $errorData;

            return true;
        }

        foreach ($errorData as $key => $val) {
            $key = lcfirst(str_replace('_', '', ucwords($key, '_')));
            if (property_exists(__CLASS__, $key)) {
                $this->{$key} = $val;
            }
        }
    }

    /**
     * @param null|string $message
     *
     * @return TargetError
     */
    public function setCode(string $message = null): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param null|string $message
     *
     * @return TargetError
     */
    public function setMessage(string $message = null): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }
}
