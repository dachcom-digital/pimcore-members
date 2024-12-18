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

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;

class OAuthResponse implements OAuthResponseInterface
{
    public function __construct(
        protected string $provider,
        protected AccessToken $accessToken,
        protected ResourceOwnerInterface $resourceOwner,
        protected array $parameter = []
    ) {
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function getAccessToken(): AccessToken
    {
        return $this->accessToken;
    }

    public function getResourceOwner(): ResourceOwnerInterface
    {
        return $this->resourceOwner;
    }

    public function getParameter(): array
    {
        return $this->parameter;
    }
}
