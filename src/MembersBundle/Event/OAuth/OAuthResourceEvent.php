<?php

namespace MembersBundle\Event\OAuth;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use MembersBundle\Adapter\User\UserInterface;
use Symfony\Component\EventDispatcher\Event;

class OAuthResourceEvent extends Event
{
    /**
     * @var UserInterface
     */
    protected $user;

    /**
     * @var ResourceOwnerInterface
     */
    protected $resourceOwner;

    /**
     * @param ResourceOwnerInterface $resourceOwner
     * @param UserInterface          $user
     */
    public function __construct(UserInterface $user, ResourceOwnerInterface $resourceOwner)
    {
        $this->resourceOwner = $resourceOwner;
        $this->user = $user;
    }

    /**
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return ResourceOwnerInterface
     */
    public function getResourceOwner()
    {
        return $this->resourceOwner;
    }
}
