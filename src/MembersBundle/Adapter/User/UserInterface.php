<?php

namespace MembersBundle\Adapter\User;

interface UserInterface extends \Symfony\Component\Security\Core\User\UserInterface
{
    const ROLE_DEFAULT = 'ROLE_USER';

    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    /**
     * @param $id
     *
     * @return mixed
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getId();

    /**
     * @param $published
     *
     * @return mixed
     */
    public function setPublished($published);

    /**
     * @return int
     */
    public function getPublished();

    /**
     * @param $confirmationToken
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
     * @param $password
     *
     * @return mixed
     */
    public function setPassword($password);

    /**
     * @return mixed
     */
    public function getPassword();

    /**
     * @param $email
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
     * @param $groups
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

}