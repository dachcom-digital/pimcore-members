<?php

namespace MembersBundle\Manager;

use MembersBundle\Adapter\Sso\SsoIdentityInterface;
use MembersBundle\Adapter\User\UserInterface;

interface SsoIdentityManagerInterface
{
    /**
     * @return SsoIdentityInterface[]
     */
    public function getSsoIdentities(UserInterface $user): array;

    public function getUserBySsoIdentity(string $provider, string $identifier): ?UserInterface;

    public function getSsoIdentity(UserInterface $user, string $provider, string $identifier): ?SsoIdentityInterface;

    public function addSsoIdentity(UserInterface $user, SsoIdentityInterface $ssoIdentity);

    public function createSsoIdentity(UserInterface $user, string $provider, string $identifier, string $profileData): SsoIdentityInterface;

    public function saveIdentity(SsoIdentityInterface $ssoIdentity): void;

    /**
     * @return SsoIdentityInterface[]
     */
    public function findExpiredSsoIdentities(int $ttl = 0): array;
}
