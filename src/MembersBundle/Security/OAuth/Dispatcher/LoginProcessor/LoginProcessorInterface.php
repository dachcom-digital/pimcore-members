<?php

namespace MembersBundle\Security\OAuth\Dispatcher\LoginProcessor;

use MembersBundle\Security\OAuth\OAuthResponse;

interface LoginProcessorInterface
{
    /**
     * @param string        $provider
     * @param OAuthResponse $oAuthResponse
     *
     * @return mixed
     */
    public function process(string $provider, OAuthResponse $oAuthResponse);
}
