<?php

namespace MembersBundle\Security\OAuth\Dispatcher\LoginProcessor;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Configuration\Configuration;
use MembersBundle\Manager\UserManagerInterface;
use MembersBundle\Registry\OAuthLoginProcessorRegistryInterface;
use MembersBundle\Security\OAuth\OAuthRegistrationHandler;
use MembersBundle\Security\OAuth\OAuthResponse;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class InstantProcessor implements LoginProcessorInterface
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
    public function process(string $provider, OAuthResponse $oAuthResponse)
    {
        /** @var UserInterface $user */
        $user = $this->userManager->createUser();

        $user->setEmail($oAuthResponse->getResourceOwner()->getId());
        $user->setPublished(true);

        $this->userManager->updateUser($user, []);

        $this->oAuthHandler->connectSsoIdentity($user, $oAuthResponse);

        return $user;
    }
}
