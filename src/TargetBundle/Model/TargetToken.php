<?php

namespace TargetBundle\Model;

/**
 * Class TargetToken
 */
class TargetToken {

    /**
     * @var null|string
     */
    private $accessToken;

    /**
     * @var null|string
     */
    private $tokenType;

    /**
     * @var null|int
     */
    private $expiresIn;

    /**
     * @var null|string
     */
    private $refreshToken;

    /**
     * @var null|int
     */
    private $tokensLeft;

    /**
     * TargetToken constructor.
     * @param array $tokenData
     */
    public function __construct(array $tokenData = []) {
        foreach($tokenData as $key => $val) {
            $key = lcfirst(str_replace('_', '', ucwords($key, '_')));
            if(property_exists(__CLASS__,$key)) {
                $this->$key = $val;
            }
        }
    }

    /**
     * @return null|string
     */
    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    /**
     * @param string|null $accessToken
     * @return TargetToken
     */
    public function setAccessToken(string $accessToken = null): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getTokenType(): ?string
    {
        return $this->tokenType;
    }

    /**
     * @param null|string $tokenType
     * @return TargetToken
     */
    public function setTokenType(string $tokenType = null): self
    {
        $this->tokenType = $tokenType;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getExpiresIn(): ?int
    {
        return $this->expiresIn;
    }

    /**
     * @param int $expiresIn
     * @return TargetToken
     */
    public function setExpiresIn(int $expiresIn = null): self
    {
        $this->expiresIn = $expiresIn;

        return $this;
    }

    /**
     * @return string
     */
    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    /**
     * @param string|null $refreshToken
     * @return TargetToken
     */
    public function setRefreshToken(string $refreshToken = null): self
    {
        $this->refreshToken = $refreshToken;

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
     * @param null|int $tokensLeft
     * @return TargetToken
     */
    public function setTokensLeft(int $tokensLeft): self
    {
        $this->tokensLeft = $tokensLeft;

        return $this;
    }

}