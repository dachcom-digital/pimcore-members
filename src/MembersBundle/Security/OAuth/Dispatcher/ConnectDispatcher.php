<?php

namespace MembersBundle\Security\OAuth\Dispatcher;

use MembersBundle\MembersEvents;
use MembersBundle\Event\OAuthEvent;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Security\OAuth\OAuthResponse;
use MembersBundle\Security\OAuth\OAuthRegistrationHandler;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
     * @var OAuthRegistrationHandler
     */
    protected $oAuthRegistrationHandler;

    /**
     * @param TokenStorageInterface       $tokenStorage
     * @param EventDispatcherInterface    $eventDispatcher
     * @param OAuthRegistrationHandler    $oAuthRegistrationHandler
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        EventDispatcherInterface $eventDispatcher,
        OAuthRegistrationHandler $oAuthRegistrationHandler
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->eventDispatcher = $eventDispatcher;
        $this->oAuthRegistrationHandler = $oAuthRegistrationHandler;
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

        $this->oAuthRegistrationHandler->connectSsoIdentity($user, $oAuthResponse);

        $this->eventDispatcher->dispatch(MembersEvents::OAUTH_PROFILE_CONNECTION_SUCCESS, new OAuthEvent($oAuthResponse));

        return $user;
    }
}
