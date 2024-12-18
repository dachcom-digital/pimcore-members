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

use MembersBundle\Adapter\Sso\SsoIdentityInterface;
use Symfony\Component\Security\Core\User\UserInterface;

interface AccountConnectorInterface
{
    public function connectToSsoIdentity(UserInterface $user, OAuthResponseInterface $oAuthResponse): SsoIdentityInterface;

    public function refreshSsoIdentityUser(UserInterface $user, OAuthResponseInterface $oAuthResponse): void;
}
