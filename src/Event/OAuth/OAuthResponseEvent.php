<?php

namespace MembersBundle\Event\OAuth;

use MembersBundle\Security\OAuth\OAuthResponseInterface;
use Symfony\Contracts\EventDispatcher\Event;

class OAuthResponseEvent extends Event
{
    public function __construct(protected OAuthResponseInterface $oauthResponse)
    {
    }

    public function getOAuthResponse(): OAuthResponseInterface
    {
        return $this->oauthResponse;
    }
}
