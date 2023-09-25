<?php

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
