<?php

namespace MembersBundle\Security\OAuth\Dispatcher;

use MembersBundle\MembersEvents;
use MembersBundle\Event\OAuthEvent;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Configuration\Configuration;
use MembersBundle\Manager\UserManagerInterface;
use MembersBundle\Security\OAuth\OAuthResponse;
use MembersBundle\Security\OAuth\OAuthRegistrationHandler;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class ConnectDispatcher implements DispatcherInterface
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var UrlGeneratorInterface
     */
    protected $router;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * @var OAuthRegistrationHandler
     */
    protected $oAuthHandler;

    /**
     * @param TokenStorageInterface       $tokenStorage
     * @param EventDispatcherInterface    $eventDispatcher
     * @param OAuthRegistrationHandler    $oAuthHandler
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        EventDispatcherInterface $eventDispatcher,
        OAuthRegistrationHandler $oAuthHandler
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->eventDispatcher = $eventDispatcher;
        $this->oAuthHandler = $oAuthHandler;
    }

    /**
     * {@inheritDoc}
     */
    public function dispatch(string $provider, OAuthResponse $oAuthResponse)
    {
        $token = $this->tokenStorage->getToken();

        if (is_null($token)) {
            throw new CustomUserMessageAuthenticationException('no valid user found to connect');
        }

        /** @var UserInterface $user */
        $user = $token->getUser();

        $this->oAuthHandler->connectSsoIdentity($user, $oAuthResponse);

        $this->eventDispatcher->dispatch(MembersEvents::OAUTH_PROFILE_CONNECTION_SUCCESS, new OAuthEvent($oAuthResponse));

        return $user;
    }
}
