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

namespace MembersBundle\Security;

use MembersBundle\Adapter\User\UserInterface as MembersUserInterface;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof MembersUserInterface) {
            return;
        }

        if (!$user->isAccountNonLocked()) {
            $ex = new LockedException('User account is locked.');
            $ex->setUser($user);

            throw $ex;
        }

        if (!$user->getPublished()) {
            $ex = new DisabledException('User account is disabled.');
            $ex->setUser($user);

            throw $ex;
        }

        if (!$user->isAccountNonExpired()) {
            $ex = new AccountExpiredException('User account has expired.');
            $ex->setUser($user);

            throw $ex;
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // do nothing
    }
}
