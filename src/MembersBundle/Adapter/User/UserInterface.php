<?php

namespace MembersBundle\Adapter\User;

use Carbon\Carbon;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface as BaseUserInterface;

interface UserInterface extends BaseUserInterface, EquatableInterface
{
    public const ROLE_DEFAULT = 'ROLE_USER';
    public const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $parentId
     *
     * @return $this
     */
    public function setParentId($parentId);

    /**
     * @return int
     */
    public function getParentId();

    /**
     * @param string $key
     *
     * @return $this
     */
    public function setKey($key);

    /**
     * @return string
     */
    public function getKey();

    /**
     * @param string $name
     * @param string $type
     * @param mixed  $data
     * @param false  $inherited
     * @param false  $inheritable
     *
     * @return $this
     */
    public function setProperty($name, $type, $data, $inherited = false, $inheritable = false);

    /**
     * @param string $name
     * @param bool   $asContainer
     *
     * @return mixed
     */
    public function getProperty($name, $asContainer = false);

    /**
     * @param bool $published
     *
     * @return $this
     */
    public function setPublished($published);

    /**
     * @return bool
     */
    public function getPublished();

    /**
     * @param string|null $confirmationToken
     *
     * @return $this
     */
    public function setConfirmationToken(?string $confirmationToken);

    public function getConfirmationToken(): ?string;

    /**
     * @param Carbon $time
     *
     * @return $this
     */
    public function setLastLogin(Carbon $time);

    public function getLastLogin(): ?Carbon;

    /**
     * @param string $password
     *
     * @return $this
     */
    public function setPassword(string $password);

    public function getPassword(): ?string;

    /**
     * @param string|null $email
     *
     * @return $this
     */
    public function setEmail(?string $email);

    public function getEmail(): ?string;

    /**
     * @param array|null $groups
     *
     * @return $this
     */
    public function setGroups(?array $groups);

    public function getGroups(): ?array;

    /**
     * @param Carbon|null $date
     *
     * @return $this
     */
    public function setPasswordRequestedAt(?Carbon $date);

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
