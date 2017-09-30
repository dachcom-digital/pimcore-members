<?php

namespace MembersBundle\Manager;

use MembersBundle\Adapter\User\UserInterface;

interface UserManagerInterface
{
    public function getClass();

    /**
     * {@inheritdoc}
     */
    public function deleteUser(UserInterface $user);

    /**
     * @param      $token
     * @param bool $includeUnpublished
     *
     * @return NULL|UserInterface
     */
    public function findUserByConfirmationToken($token, $includeUnpublished = TRUE);

    /**
     * @param      $emailAddress
     * @param bool $includeUnpublished
     *
     * @return NULL|UserInterface
     */
    public function findUserByEmail($emailAddress, $includeUnpublished = TRUE);

    /**
     * @fixme: includeUnpublished?
     *
     * @param  string $username
     * @param bool    $includeUnpublished
     *
     * @return NULL|UserInterface
     */
    public function findUserByUsername($username, $includeUnpublished = TRUE);

    /**
     * {@inheritdoc}
     */
    public function findUserByCondition($condition = '', $conditionVariables = [], $includeUnpublished = TRUE);

    /**
     * {@inheritdoc}
     */
    public function findUserByUsernameOrEmail($usernameOrEmail);

    /**
     * {@inheritdoc}
     */
    public function findUsers();

    /**
     * {@inheritdoc}
     */
    public function reloadUser(UserInterface $user);

    /**
     * @return UserInterface
     */
    public function createUser();

    /**
     * @param UserInterface $user
     * @param array         $properties
     *
     * @return mixed
     */
    public function updateUser(UserInterface $user, $properties = []);
}