<?php

namespace MembersBundle\Security\OAuth\Dispatcher;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Registry\OAuthLoginProcessorRegistryInterface;
use MembersBundle\Configuration\Configuration;
use MembersBundle\Security\OAuth\OAuthResponse;

class LoginDispatcher implements DispatcherInterface
{
    protected Configuration $configuration;
    protected OAuthLoginProcessorRegistryInterface $loginProcessorRegistry;

    public function __construct(
        Configuration $configuration,
        OAuthLoginProcessorRegistryInterface $loginProcessorRegistry
    ) {
        $this->configuration = $configuration;
        $this->loginProcessorRegistry = $loginProcessorRegistry;
    }

    public function dispatch(string $provider, OAuthResponse $oAuthResponse): ?UserInterface
    {
        $activationStrategyName = $this->configuration->getOAuthConfig('activation_type');

        if ($this->loginProcessorRegistry->has($activationStrategyName) === false) {
            throw new \Exception(sprintf('no dispatcher with identifier %s found', $provider));
        }

        return $this->loginProcessorRegistry->get($activationStrategyName)->process($provider, $oAuthResponse);
    }
}
