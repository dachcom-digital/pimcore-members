<?php

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
