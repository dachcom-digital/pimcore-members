<?php

namespace MembersBundle\Security\OAuth\Dispatcher\LoginProcessor;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Security\OAuth\OAuthResponse;

interface LoginProcessorInterface
{
    public function process(string $provider, OAuthResponse $oAuthResponse): ?UserInterface;
}
