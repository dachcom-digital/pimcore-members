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

namespace MembersBundle\Event\OAuth;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use MembersBundle\Adapter\User\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

class OAuthResourceEvent extends Event
{
    public function __construct(
        protected UserInterface $user,
        protected ResourceOwnerInterface $resourceOwner
    ) {
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function getResourceOwner(): ResourceOwnerInterface
    {
        return $this->resourceOwner;
    }
}
