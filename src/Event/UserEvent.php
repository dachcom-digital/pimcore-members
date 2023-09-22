<?php

namespace MembersBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use MembersBundle\Adapter\User\UserInterface;

class UserEvent extends Event
{
    protected ?UserInterface $user = null;
    protected ?Request $request = null;

    public function __construct(?UserInterface $user, ?Request $request = null)
    {
        $this->user = $user;
        $this->request = $request;
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
