<?php

namespace MembersBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use MembersBundle\Adapter\User\UserInterface;

class UserEvent extends Event
{
    /**
     * @var null|Request
     */
    private $request;

    /**
     * @var UserInterface
     */
    private $user;

    /**
     * UserEvent constructor.
     *
     * @param UserInterface $user
     * @param Request|null  $request
     */
    public function __construct(UserInterface $user, Request $request = null)
    {
        $this->user = $user;
        $this->request = $request;
    }

    /**
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }
}