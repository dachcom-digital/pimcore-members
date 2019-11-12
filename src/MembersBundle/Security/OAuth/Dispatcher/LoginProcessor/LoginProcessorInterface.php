<?php

namespace MembersBundle\Security\OAuth\Dispatcher\LoginProcessor;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Security\OAuth\OAuthResponse;

interface LoginProcessorInterface
{
    /**
     * @param string        $provider
     * @param OAuthResponse $oAuthResponse
     *
     * @return UserInterface|null
     */
    public function process(string $provider, OAuthResponse $oAuthResponse);
}
