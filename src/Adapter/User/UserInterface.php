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
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface as BaseUserInterface;

interface UserInterface extends BaseUserInterface, EquatableInterface
{
    public const ROLE_DEFAULT = 'ROLE_USER';
    public const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    public function setId(?int $id): static;

    public function getId(): ?int;

    public function setParentId(?int $parentId): static;

    public function getParentId(): ?int;

    public function setKey(string $key): static;

    public function getKey(): ?string;

    public function setProperty(string $name, string $type, mixed $data, bool $inherited = false, bool $inheritable = false): static;

    public function getProperty(string $name, bool $asContainer = false): mixed;

    public function setPublished(bool $published): static;

    public function getPublished(): bool;

    public function setConfirmationToken(?string $confirmationToken): static;

    public function getConfirmationToken(): ?string;

    public function setLastLogin(Carbon $time): static;

    public function getLastLogin(): ?Carbon;

    public function setPassword(string $password): static;

    public function getPassword(): ?string;

    public function setUserName(?string $userName): static;

    public function getUserName(): ?string;

    public function setEmail(?string $email): static;

    public function getEmail(): ?string;

    public function setGroups(array $groups): static;

    public function getGroups(): ?array;

    public function setPasswordRequestedAt(?Carbon $date): static;

    public function getPasswordRequestedAt(): ?Carbon;

    /**
     * Checks whether the password reset request has expired.
     *
     * @param int $ttl Requests older than this many seconds will be considered expired
     */
    public function isPasswordRequestNonExpired(int $ttl): bool;

    public function setPlainPassword(string $password): self;

    public function getPlainPassword(): ?string;

    public function isAccountNonExpired(): bool;

    public function isAccountNonLocked(): bool;

    /**
     * @throws \Exception
     */
    public function save();
}
