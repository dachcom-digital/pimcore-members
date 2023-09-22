<?php

namespace MembersBundle\Manager;

use MembersBundle\Adapter\Sso\SsoIdentityInterface;
use MembersBundle\Adapter\User\UserInterface;

interface SsoIdentityManagerInterface
{
    public function getClass(): string;

    public function getUserBySsoIdentity(string $provider, string $identifier): ?UserInterface;

    /**
     * @return array<int, SsoIdentityInterface>
     */
    public function findExpiredSsoIdentities(int $ttl = 0): array;

    /**
     * @return array<int, SsoIdentityInterface>
     */
    public function getSsoIdentities(UserInterface $user): array;

    public function getSsoIdentity(UserInterface $user, string $provider, string $identifier): ?SsoIdentityInterface;

    public function addSsoIdentity(UserInterface $user, SsoIdentityInterface $ssoIdentity): void;

    public function createSsoIdentity(UserInterface $user, string $provider, string $identifier, string $profileData): SsoIdentityInterface;

    /**
     * @throws \Exception
     */
    public function saveIdentity(SsoIdentityInterface $ssoIdentity): void;
}
