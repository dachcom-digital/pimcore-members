<?php

namespace MembersBundle\Security\OAuth\Dispatcher;

use MembersBundle\MembersEvents;
use MembersBundle\Event\OAuth\OAuthResponseEvent;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Security\OAuth\OAuthResponse;
use MembersBundle\Security\OAuth\OAuthRegistrationHandler;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class ConnectDispatcher implements DispatcherInterface
{
    public function __construct(
        protected TokenStorageInterface $tokenStorage,
        protected EventDispatcherInterface $eventDispatcher,
        protected OAuthRegistrationHandler $oAuthRegistrationHandler
    ) {
    }

    public function dispatch(string $provider, OAuthResponse $oAuthResponse): UserInterface
    {
        $token = $this->tokenStorage->getToken();

        if (is_null($token)) {
            throw new CustomUserMessageAuthenticationException('no valid user found to connect');
        }

        /** @var UserInterface $user */
        $user = $token->getUser();

        $this->oAuthRegistrationHandler->connectSsoIdentity($user, $oAuthResponse);

        $this->eventDispatcher->dispatch(new OAuthResponseEvent($oAuthResponse), MembersEvents::OAUTH_PROFILE_CONNECTION_SUCCESS);

        return $user;
    }
}
