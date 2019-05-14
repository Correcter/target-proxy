<?php

namespace UserBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Sergey Ageev (Cimus <s_ageev@mail.ru>)
 */
class ApiUser implements UserInterface
{
    private $username;
    private $token;

    public function __construct(string $username, $token)
    {
        $this->username = $username;
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    public function eraseCredentials()
    {
    }

    public function getPassword(): string
    {
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return ['ROLE_API_USER'];
    }

    public function getSalt()
    {
    }

    public function getUsername(): string
    {
        return $this->username;
    }
}
