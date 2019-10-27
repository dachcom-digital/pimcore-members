<?php

namespace MembersBundle\Security\OAuth\Dispatcher;

use MembersBundle\Registry\OAuthLoginProcessorRegistryInterface;
use MembersBundle\Configuration\Configuration;
use MembersBundle\Manager\UserManagerInterface;
use MembersBundle\Security\OAuth\OAuthResponse;
use MembersBundle\Security\OAuth\OAuthRegistrationHandler;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class LoginDispatcher implements DispatcherInterface
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var OAuthRegistrationHandler
     */
    protected $authRegistrationHandler;

    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * @var OAuthRegistrationHandler
     */
    protected $oAuthHandler;

    /**
     * @var OAuthLoginProcessorRegistryInterface
     */
    protected $loginProcessorRegistry;

    /**
     * @param EventDispatcherInterface             $eventDispatcher
     * @param Configuration                        $configuration
     * @param OAuthRegistrationHandler             $authRegistrationHandler
     * @param UserManagerInterface                 $userManager
     * @param OAuthRegistrationHandler             $oAuthHandler
     * @param OAuthLoginProcessorRegistryInterface $loginProcessorRegistry
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        Configuration $configuration,
        OAuthRegistrationHandler $authRegistrationHandler,
        UserManagerInterface $userManager,
        OAuthRegistrationHandler $oAuthHandler,
        OAuthLoginProcessorRegistryInterface $loginProcessorRegistry
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->configuration = $configuration;
        $this->authRegistrationHandler = $authRegistrationHandler;
        $this->userManager = $userManager;
        $this->oAuthHandler = $oAuthHandler;
        $this->loginProcessorRegistry = $loginProcessorRegistry;
    }

    /**
     * {@inheritDoc}
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
