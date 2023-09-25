<?php

namespace MembersBundle\Event\OAuth;

use MembersBundle\Adapter\User\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

class OAuthIdentityEvent extends Event
{
    protected UserInterface $user;
    protected bool $dispatchStatus = false;

    public function __construct(UserInterface $user)
    {
        $this->user = $user;
    }

    public function getIdentity(): UserInterface
    {
        return $this->user;
    }

    public function setIdentityDispatchStatus(bool $status): void
    {
        $this->dispatchStatus = $status;
    }

    /**
     * @internal
     */
    public function identityCanDispatch(): bool
    {
        return $this->dispatchStatus;
    }
}
