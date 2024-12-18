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

namespace MembersBundle\Event;

use MembersBundle\Adapter\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class UserEvent extends Event
{
    public function __construct(
        protected ?UserInterface $user = null,
        protected ?Request $request = null
    ) {
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function getRequest(): ?Request
    {
        return $this->request;
    }
}
