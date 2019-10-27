<?php

namespace MembersBundle\Security\OAuth\Dispatcher\Router;

use MembersBundle\Security\OAuth\Dispatcher\DispatcherInterface;
use MembersBundle\Security\OAuth\OAuthResponse;

class DispatchRouter
{
    /**
     * @var array
     */
    protected $dispatcher;

    /**
     * @param string              $dispatcherName
     * @param DispatcherInterface $dispatcher
     */
    public function register(string $dispatcherName, DispatcherInterface $dispatcher)
    {
        $this->dispatcher[$dispatcherName] = $dispatcher;
    }

    /**
     * @param string        $dispatcherName
     * @param string        $provider
     * @param OAuthResponse $oAuthResponse
     *
     * @return mixed
     */
    public function dispatch(string $dispatcherName, string $provider, OAuthResponse $oAuthResponse)
    {
        /** @var DispatcherInterface $dispatcher */
        $dispatcher = $this->dispatcher[$dispatcherName];

        return $dispatcher->dispatch($provider, $oAuthResponse);
    }
}
