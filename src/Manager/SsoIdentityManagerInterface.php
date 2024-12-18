<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

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
