<?php

namespace MembersBundle\Security\OAuth\Dispatcher;

use MembersBundle\Security\OAuth\OAuthResponse;

interface DispatcherInterface
{
    /**
     * @param string        $provider
     * @param OAuthResponse $oAuthResponse
     *
     * @return mixed
     */
    public function dispatch(string $provider, OAuthResponse $oAuthResponse);
}
