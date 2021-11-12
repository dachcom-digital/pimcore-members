<?php

namespace MembersBundle\Adapter\User;

use Carbon\Carbon;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface as BaseUserInterface;

interface UserInterface extends BaseUserInterface, EquatableInterface
{
    public const ROLE_DEFAULT = 'ROLE_USER';
    public const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    public function setId(int $id);

    public function getId();

    public function setParentId(int $parentId);

    public function getParentId();

    public function setKey(string $key);

    public function getKey();

    public function setProperty(string $name, string $type, mixed $data, bool $inherited = false, bool $inheritable = false);

    public function getProperty(string $name, bool $asContainer = false);

    public function setPublished(bool $published);

    public function getPublished();

    public function setConfirmationToken(?string $confirmationToken): self;

    public function getConfirmationToken(): ?string;

    public function setLastLogin(Carbon $time): self;

    public function getLastLogin(): ?Carbon;

    public function setPassword(string $password): self;

    public function getPassword(): ?string;

    public function setEmail(string $email): self;

    public function getEmail(): ?string;

    public function getPlainPassword(): string;

    public function setGroups(array $groups): self;

    public function getGroups(): ?array;

    public function setPasswordRequestedAt(Carbon $date): self;

    public function getPasswordRequestedAt(): ?Carbon;

    /**
     * Checks whether the password reset request has expired.
     *
     * @param int $ttl Requests older than this many seconds will be considered expired
     */
    public function isPasswordRequestNonExpired(int $ttl): bool;

    public function isAccountNonExpired(): bool;

    public function isAccountNonLocked(): bool;

    /**
     * @throws \Exception
     */
    public function save();
}
