<?php

namespace MembersBundle\Manager;

use MembersBundle\Adapter\User\UserInterface;

interface UserManagerInterface
{
    /**
     * @return string
     */
    public function getClass();

    /**
     * @param UserInterface $user
     *
     * @return mixed
     */
    public function deleteUser(UserInterface $user);

    /**
     * @param string $token
     * @param bool   $includeUnpublished
     *
     * @return null|UserInterface
     */
    public function findUserByConfirmationToken($token, $includeUnpublished = true);

    /**
     * @param string $emailAddress
     * @param bool   $includeUnpublished
     *
     * @return null|UserInterface
     */
    public function findUserByEmail($emailAddress, $includeUnpublished = true);

    /**
     * @param string $username
     * @param bool   $includeUnpublished
     *
     * @return null|UserInterface
     */
    public function findUserByUsername($username, $includeUnpublished = true);

    /**
     * @param int  $userId
     * @param bool $includeUnpublished
     *
     * @return null|UserInterface
     */
    public function findUserById($userId, $includeUnpublished = true);

    /**
     * @param string $condition
     * @param array  $conditionVariables
     * @param bool   $includeUnpublished
     * @param bool   $returnSingle
     *
     * @return null|array|UserInterface
     */
    public function findUserByCondition($condition = '', $conditionVariables = [], $includeUnpublished = true, $returnSingle = true);

    /**
     * @param string $usernameOrEmail
     *
     * @return null|array|UserInterface
     */
    public function findUserByUsernameOrEmail($usernameOrEmail);

    /**
     * @return array
     */
    public function findUsers();

    /**
     * @param UserInterface $user
     *
     * @return void
     * @throws \Exception
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
     * @return UserInterface
     */
    public function updateUser(UserInterface $user, $properties = []);
}
