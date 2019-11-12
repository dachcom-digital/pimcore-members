<?php

namespace MembersBundle\Security\OAuth\Dispatcher\LoginProcessor;

use MembersBundle\Security\OAuth\OAuthRegistrationHandler;
use MembersBundle\Security\OAuth\OAuthResponse;

class InstantProcessor implements LoginProcessorInterface
{
    /**
     * @var OAuthRegistrationHandler
     */
    protected $oAuthRegistrationHandler;

    /**
     * @param OAuthRegistrationHandler $oAuthRegistrationHandler
     */
    public function __construct(OAuthRegistrationHandler $oAuthRegistrationHandler)
    {
        $this->oAuthRegistrationHandler = $oAuthRegistrationHandler;
    }

    /**
     * {@inheritDoc}
     */
    public function process(string $provider, OAuthResponse $oAuthResponse)
    {
        return $this->oAuthRegistrationHandler->connectNewUserWithSsoIdentity($oAuthResponse);
    }
}
