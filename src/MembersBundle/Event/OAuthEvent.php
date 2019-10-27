<?php

namespace MembersBundle\Event;

use MembersBundle\Security\OAuth\OAuthResponseInterface;
use Symfony\Component\EventDispatcher\Event;

class OAuthEvent extends Event
{
    /**
     * @var OAuthResponseInterface
     */
    protected $oauthResponse;

    /**
     * @param OAuthResponseInterface $oauthResponse
     */
    public function __construct(OAuthResponseInterface $oauthResponse)
    {
        $this->oauthResponse = $oauthResponse;
    }

    /**
     * @return OAuthResponseInterface
     */
    public function getOAuthResponse()
    {
        return $this->oauthResponse;
    }
}
