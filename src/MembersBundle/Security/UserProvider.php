<?php

namespace MembersBundle\Security;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Manager\UserManagerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface as SecurityUserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    protected UserManagerInterface $userManager;

    public function __construct(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }

    public function loadUserByIdentifier(string $identifier)
    {
        $user = $this->findUser($identifier);

        if (!$user) {
            throw new UserNotFoundException(sprintf('Username "%s" does not exist.', $identifier));
        }

        return $user;
    }

    public function loadUserByUsername(string $username)
    {
        $user = $this->findUser($username);

        if (!$user) {
            throw new UserNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }

        return $user;
    }

    public function refreshUser(SecurityUserInterface $user)
    {
        if (!$user instanceof UserInterface) {
            throw new UnsupportedUserException(sprintf('Expected an instance of MembersBundle\Adapter\User\UserInterface, but got "%s".', get_class($user)));
        }

        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Expected an instance of %s, but got "%s".', $this->userManager->getClass(), get_class($user)));
        }

        if (null === $reloadedUser = $this->userManager->findUserByCondition('oo_id = ?', [(int) $user->getId()])) {
            throw new UserNotFoundException(sprintf('User with ID "%s" could not be reloaded.', $user->getId()));
        }

        return $reloadedUser;
    }

    public function supportsClass($class)
    {
        $userClass = $this->userManager->getClass();

        return $userClass === $class || is_subclass_of($class, $userClass);
    }

    protected function findUser(string $username): ?UserInterface
    {
        return $this->userManager->findUserByUsername($username);
    }
}
