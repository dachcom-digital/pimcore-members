<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace MembersBundle\Security\OAuth\Dispatcher;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Event\OAuth\OAuthResponseEvent;
use MembersBundle\MembersEvents;
use MembersBundle\Security\OAuth\OAuthRegistrationHandler;
use MembersBundle\Security\OAuth\OAuthResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

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
