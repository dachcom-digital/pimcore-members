<?php

namespace MembersBundle\Security\OAuth\Dispatcher;

use MembersBundle\Registry\OAuthLoginProcessorRegistryInterface;
use MembersBundle\Configuration\Configuration;
use MembersBundle\Security\OAuth\OAuthResponse;

class LoginDispatcher implements DispatcherInterface
{
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var OAuthLoginProcessorRegistryInterface
     */
    protected $loginProcessorRegistry;

    /**
     * @param Configuration                        $configuration
     * @param OAuthLoginProcessorRegistryInterface $loginProcessorRegistry
     */
    public function __construct(
        Configuration $configuration,
        OAuthLoginProcessorRegistryInterface $loginProcessorRegistry
    ) {
        $this->configuration = $configuration;
        $this->loginProcessorRegistry = $loginProcessorRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(string $provider, OAuthResponse $oAuthResponse)
    {
        $activationStrategyName = $this->configuration->getOAuthConfig('activation_type');

        if ($this->loginProcessorRegistry->has($activationStrategyName) === false) {
            throw new \Exception(sprintf('no dispatcher with identifier %s found', $provider));
        }

        $processor = $this->loginProcessorRegistry->get($activationStrategyName);

        return $processor->process($provider, $oAuthResponse);
    }
}
