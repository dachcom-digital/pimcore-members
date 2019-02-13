<?php

namespace MembersBundle\Adapter\User;

interface UserInterface extends \Symfony\Component\Security\Core\User\UserInterface
{
    const ROLE_DEFAULT = 'ROLE_USER';

    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    /**
     * @param int $id
     *
     * @return mixed
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
     * @param bool   $inherited
     * @param bool   $inheritable
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
     * @return mixed
     */
    public function setPublished($published);

    /**
     * @return int
     */
    public function getPublished();

    /**
     * @param string $confirmationToken
     *
     * @return mixed
     */
    public function setConfirmationToken($confirmationToken);

    /**
     * @return mixed
     */
    public function getConfirmationToken();

    /**
     * @param \Carbon\Carbon $time
     *
     * @return mixed
     */
    public function setLastLogin($time);

    /**
     * @return mixed
     */
    public function getLastLogin();

    /**
     * @param string $password
     *
     * @return mixed
     */
    public function setPassword($password);

    /**
     * @return mixed
     */
    public function getPassword();

    /**
     * @param string $email
     *
     * @return mixed
     */
    public function setEmail($email);

    /**
     * @return mixed
     */
    public function getEmail();

    /**
     * Gets the plain password.
     *
     * @return string
     */
    public function getPlainPassword();

    /**
     * @param array $groups
     *
     * @return mixed
     */
    public function setGroups($groups);

    /**
     * @return mixed
     */
    public function getGroups();

    /**
     * @param \Carbon\Carbon $date
     *
     * @return mixed
     */
    public function setPasswordRequestedAt($date);

    /**
     * @return mixed
     */
    public function getPasswordRequestedAt();

    /**
     * Checks whether the password reset request has expired.
     *
     * @param int $ttl Requests older than this many seconds will be considered expired
     *
     * @return int
     */
    public function isPasswordRequestNonExpired($ttl);

    /**
     * @return mixed
     */
    public function isAccountNonExpired();

    /**
     * @return mixed
     */
    public function isAccountNonLocked();

    /**
     * @return $this
     *
     * @throws \Exception
     */
    public function save();
}
