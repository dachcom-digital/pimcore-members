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

namespace MembersBundle\Security\OAuth;

class OAuthScopeAllocator implements OAuthScopeAllocatorInterface
{
    public function __construct(protected array $scopes)
    {
    }

    public function allocate(string $client): array
    {
        if (!isset($this->scopes[$client])) {
            return [];
        }

        if (!is_array($this->scopes[$client])) {
            return [];
        }

        return $this->scopes[$client];
    }
}
