<?php

namespace MembersBundle\Event\OAuth;

use MembersBundle\Adapter\User\UserInterface;
use Symfony\Component\EventDispatcher\Event;

class OAuthIdentityEvent extends Event
{
    /**
     * @var UserInterface
     */
    protected $user;

    /**
     * @var bool
     */
    protected $dispatchStatus;

    /**
     * @param UserInterface $user
     */
    public function __construct(UserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * @return UserInterface
     */
    public function getIdentity()
    {
        return $this->user;
    }

    /**
     * @param bool $status
     */
    public function setIdentityDispatchStatus(bool $status)
    {
        $this->dispatchStatus = $status;
    }

    /**
     * @return bool
     *
     * @internal
     */
    public function identityCanDispatch()
    {
        return $this->dispatchStatus;
    }
}
