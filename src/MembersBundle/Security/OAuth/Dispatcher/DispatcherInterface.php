<?php

namespace MembersBundle\Security\OAuth\Dispatcher;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Security\OAuth\OAuthResponse;

interface DispatcherInterface
{
    public function dispatch(string $provider, OAuthResponse $oAuthResponse): ?UserInterface;
}
