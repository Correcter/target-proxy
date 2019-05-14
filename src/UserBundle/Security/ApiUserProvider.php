<?php

namespace UserBundle\Security;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @author Sergey Ageev (Cimus <s_ageev@mail.ru>)
 */
class ApiUserProvider implements UserProviderInterface
{
    private $tokenByUser;
    private $userByToken;

    public function __construct(array $apiUsers)
    {
        $this->userByToken = $apiUsers;
        $this->tokenByUser = array_flip($apiUsers);
    }

    /**
     * @param string $token
     *
     * @throws UsernameNotFoundException
     *
     * @return UserInterface
     */
    public function loadUserByToken($token): UserInterface
    {
        if (isset($this->tokenByUser[$token])) {
            return new ApiUser($this->tokenByUser[$token], $token);
        }

        throw new UsernameNotFoundException(
            sprintf('User for token "%s" does not exist.', $token)
        );
    }

    /**
     * @param string $username
     *
     * @throws UsernameNotFoundException
     *
     * @return UserInterface
     */
    public function loadUserByUsername($username): UserInterface
    {
        if (isset($this->userByToken[$username])) {
            return new ApiUser($username, $this->userByToken[$username]);
        }

        throw new UsernameNotFoundException(
            sprintf('Username "%s" does not exist.', $username)
        );
    }

    /**
     * @param UserInterface $user
     *
     * @throws UnsupportedUserException
     *
     * @return UserInterface
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof ApiUser) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class): bool
    {
        return ApiUser::class === $class;
    }
}
