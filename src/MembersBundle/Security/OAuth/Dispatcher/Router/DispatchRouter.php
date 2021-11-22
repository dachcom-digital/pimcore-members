<?php

namespace MembersBundle\Security\OAuth\Dispatcher\Router;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Security\OAuth\Dispatcher\DispatcherInterface;
use MembersBundle\Security\OAuth\OAuthResponse;

class DispatchRouter
{
    protected array $dispatcher = [];

    public function register(string $dispatcherName, DispatcherInterface $dispatcher): void
    {
        $this->dispatcher[$dispatcherName] = $dispatcher;
    }

    public function dispatch(string $dispatcherName, string $provider, OAuthResponse $oAuthResponse): ?UserInterface
    {
        /** @var DispatcherInterface $dispatcher */
        $dispatcher = $this->dispatcher[$dispatcherName];

        return $dispatcher->dispatch($provider, $oAuthResponse);
    }
}
