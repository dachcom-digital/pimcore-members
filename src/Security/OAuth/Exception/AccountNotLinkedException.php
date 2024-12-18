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

namespace MembersBundle\Security\OAuth\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AccountNotLinkedException extends AuthenticationException
{
    protected ?string $registrationKey = null;

    public function setRegistrationKey(string $registrationKey): void
    {
        $this->registrationKey = $registrationKey;
    }

    public function getRegistrationKey(): ?string
    {
        return $this->registrationKey;
    }
}
