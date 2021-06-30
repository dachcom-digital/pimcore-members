<?php

namespace MembersBundle\Event;

use Pimcore\Model\DataObject\AbstractObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class StaticRouteEvent extends Event
{
    protected Request $request;
    protected ?string $routeName;
    protected ?AbstractObject $object;

    public function __construct(Request $request, ?string $routeName = null)
    {
        $this->request = $request;
        $this->routeName = $routeName;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getRouteName(): ?string
    {
        return $this->routeName;
    }

    public function setStaticRouteObject(AbstractObject $object)
    {
        $this->object = $object;
    }

    public function getStaticRouteObject(): ?AbstractObject
    {
        return $this->object;
    }
}
