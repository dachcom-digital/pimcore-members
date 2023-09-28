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
    public function __construct(
        protected string $authIdentifier,
        protected UserManagerInterface $userManager
    ) {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->findUser($identifier);

        if (!$user) {
            throw new UserNotFoundException(sprintf('User with identifier "%s" does not exist.', $identifier));
        }

        return $user;
    }

    public function refreshUser(SecurityUserInterface $user): UserInterface
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

    public function supportsClass(string $class): bool
    {
        $userClass = $this->userManager->getClass();

        return $userClass === $class || is_subclass_of($class, $userClass);
    }

    /**
     * Finds a user by username or email address.
     * This method is meant to be an extension point for child classes.
     */
    protected function findUser(string $identifier): ?UserInterface
    {
        return $this->authIdentifier === 'email'
            ? $this->userManager->findUserByEmail($identifier)
            : $this->userManager->findUserByUsername($identifier);
    }
}
