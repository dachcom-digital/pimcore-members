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

namespace MembersBundle\Adapter\User;

use Carbon\Carbon;
use Pimcore\Model\Element\ElementInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface as BaseUserInterface;

interface UserInterface extends BaseUserInterface, EquatableInterface, ElementInterface
{
    public const ROLE_DEFAULT = 'ROLE_USER';
    public const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    public function setPublished(bool $published): static;

    public function getPublished(): bool;

    public function setConfirmationToken(?string $confirmationToken);

    public function getConfirmationToken(): ?string;

    public function setLastLogin(Carbon $time);

    public function getLastLogin(): ?Carbon;

    public function setPassword(?string $password);

    public function getPassword(): ?string;

    public function setUserName(?string $userName);

    public function getUserName(): ?string;

    public function setEmail(?string $email);

    public function getEmail(): ?string;

    public function setGroups(array $groups);

    public function getGroups(): ?array;

    public function setPasswordRequestedAt(?Carbon $date);

    public function getPasswordRequestedAt(): ?Carbon;

    /**
     * Checks whether the password reset request has expired.
     *
     * @param int $ttl Requests older than this many seconds will be considered expired
     */
    public function isPasswordRequestNonExpired(int $ttl): bool;

    public function setPlainPassword(string $password);

    public function getPlainPassword(): ?string;

    public function isAccountNonExpired(): bool;

    public function isAccountNonLocked(): bool;

}
