<?php

namespace MembersBundle\Event\OAuth;

use MembersBundle\Security\OAuth\OAuthResponseInterface;
use Symfony\Contracts\EventDispatcher\Event;

class OAuthResponseEvent extends Event
{
    protected OAuthResponseInterface $oauthResponse;

    public function __construct(OAuthResponseInterface $oauthResponse)
    {
        $this->oauthResponse = $oauthResponse;
    }

    public function getOAuthResponse(): OAuthResponseInterface
    {
        return $this->oauthResponse;
    }
}
