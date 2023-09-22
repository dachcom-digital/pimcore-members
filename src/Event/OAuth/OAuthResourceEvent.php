<?php

namespace MembersBundle\Event\OAuth;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use MembersBundle\Adapter\User\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

class OAuthResourceEvent extends Event
{
    protected UserInterface $user;
    protected ResourceOwnerInterface $resourceOwner;

    public function __construct(UserInterface $user, ResourceOwnerInterface $resourceOwner)
    {
        $this->resourceOwner = $resourceOwner;
        $this->user = $user;
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
