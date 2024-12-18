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

use Pimcore\Model\DataObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class StaticRouteEvent extends Event
{
    protected ?DataObject $object = null;

    public function __construct(
        protected Request $request,
        protected ?string $routeName = null
    ) {
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getRouteName(): ?string
    {
        return $this->routeName;
    }

    public function setStaticRouteObject(DataObject $object): void
    {
        $this->object = $object;
    }

    public function getStaticRouteObject(): ?DataObject
    {
        return $this->object;
    }
}
