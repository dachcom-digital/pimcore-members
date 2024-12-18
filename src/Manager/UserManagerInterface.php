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

use MembersBundle\Adapter\User\UserInterface;

interface UserManagerInterface
{
    public function getClass(): string;

    public function deleteUser(UserInterface $user): void;

    public function findUserByConfirmationToken(string $token, bool $includeUnpublished = true): ?UserInterface;

    public function findUserByEmail(string $emailAddress, bool $includeUnpublished = true): ?UserInterface;

    public function findUserByUsername(string $username, bool $includeUnpublished = true): ?UserInterface;

    public function findUserById(int $userId, bool $includeUnpublished = true): ?UserInterface;

    public function findUserByCondition(
        string $condition = '',
        array $conditionVariables = [],
        bool $includeUnpublished = true,
        bool $returnSingle = true
    ): ?UserInterface;

    public function findUserByUsernameOrEmail(string $usernameOrEmail): ?UserInterface;

    /**
     * @return array<int, UserInterface>
     */
    public function findUsers(): array;

    /**
     * @throws \Exception
     */
    public function reloadUser(UserInterface $user): void;

    public function createUser(): UserInterface;

    public function createAnonymousUser(string $key): UserInterface;

    public function updateUser(UserInterface $user, array $properties = []): UserInterface;
}
