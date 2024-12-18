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

interface OAuthTokenStorageInterface
{
    public function saveToken(string $key, OAuthResponseInterface $OAuthResponse): void;

    public function destroyToken(string $key): void;

    public function loadToken(string $key, int $maxLifetime = 300): ?OAuthResponseInterface;
}
