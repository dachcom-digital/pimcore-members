<?php

namespace MembersBundle\Security\OAuth\Dispatcher\LoginProcessor;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Security\OAuth\OAuthRegistrationHandler;
use MembersBundle\Security\OAuth\OAuthResponse;

class InstantProcessor implements LoginProcessorInterface
{
    public function __construct(protected OAuthRegistrationHandler $oAuthRegistrationHandler)
    {
    }

    public function process(string $provider, OAuthResponse $oAuthResponse): ?UserInterface
    {
        return $this->oAuthRegistrationHandler->connectNewUserWithSsoIdentity($oAuthResponse);
    }
}
