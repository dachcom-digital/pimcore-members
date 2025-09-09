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

use Pimcore\Model\DataObject\ClassDefinition\Data\Password;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherInterface;

class PimcorePasswordHasher implements PasswordHasherInterface
{
    public function hash(string $plainPassword): string
    {
        // Use PHP's password_hash function which is what Pimcore uses
        return password_hash($plainPassword, PASSWORD_DEFAULT);
    }

    public function verify(string $hashedPassword, string $plainPassword): bool
    {
        // Use PHP's password_verify function which is what Pimcore uses
        return password_verify($plainPassword, $hashedPassword);
    }

    public function needsRehash(string $hashedPassword): bool
    {
        // Check if the password needs to be rehashed
        return password_needs_rehash($hashedPassword, PASSWORD_DEFAULT);
    }
}
