<?php

namespace MembersBundle\Security\OAuth\Dispatcher;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Security\OAuth\OAuthResponse;

interface DispatcherInterface
{
    /**
     * @param string        $provider
     * @param OAuthResponse $oAuthResponse
     *
     * @return UserInterface|null
     */
    public function dispatch(string $provider, OAuthResponse $oAuthResponse);
}
