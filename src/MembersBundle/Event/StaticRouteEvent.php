<?php

namespace MembersBundle\Event;

use Pimcore\Model\DataObject\AbstractObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\Event;

class StaticRouteEvent extends Event
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Request
     */
    protected $routeName;

    /**
     * @var AbstractObject
     */
    protected $object;

    /**
     * FilterUserResponseEvent constructor.
     *
     * @param Request $request
     * @param string $routeName
     */
    public function __construct($request, $routeName = NULL)
    {
        $this->request = $request;
        $this->routeName = $routeName;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return Request
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    public function setStaticRouteObject(AbstractObject $object)
    {
        $this->object = $object;
    }

    /**
     * @return AbstractObject
     */
    public function getStaticRouteObject()
    {
        return $this->object;
    }
}
