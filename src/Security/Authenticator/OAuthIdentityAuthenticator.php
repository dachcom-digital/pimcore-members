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

namespace MembersBundle\Security\Authenticator;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Security\OAuth\Dispatcher\Router\DispatchRouter;
use MembersBundle\Security\OAuth\Exception\AccountNotLinkedException;
use MembersBundle\Security\OAuth\OAuthRegistrationHandler;
use MembersBundle\Security\OAuth\OAuthResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

class OAuthIdentityAuthenticator extends OAuth2Authenticator implements AuthenticationEntrypointInterface
{
    public function __construct(
        protected UrlGeneratorInterface $router,
        protected ClientRegistry $clientRegistry,
        protected OAuthRegistrationHandler $oAuthRegistrationHandler,
        protected DispatchRouter $dispatchRouter
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'members_user_security_oauth_check';
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new RedirectResponse(
            $this->router->generate('members_user_security_login'),
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }

    public function authenticate(Request $request): Passport
    {
        /** @var AttributeBagInterface $sessionBag */
        $sessionBag = $request->getSession()->getBag('members_session');

        if (!$sessionBag->has('oauth_state_data')) {
            throw new CustomUserMessageAuthenticationException('No oauth_state_data given');
        }

        $data = $sessionBag->get('oauth_state_data');

        $provider = $data['provider'];
        $type = $data['type'];
        $parameter = $data['parameter'] ?? [];

        $client = $this->getClient($provider);
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client, $provider, $type, $parameter) {
                $user = $client->fetchUserFromToken($accessToken);
                $oAuthResponse = new OAuthResponse($provider, $accessToken, $user, $parameter);
                $memberUser = $this->oAuthRegistrationHandler->getRefreshedUserFromUserResponse($oAuthResponse);

                if ($memberUser instanceof UserInterface) {
                    return $memberUser;
                }

                return $this->dispatchRouter->dispatch($type, $provider, $oAuthResponse);
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        /** @var AttributeBagInterface $sessionBag */
        $sessionBag = $request->getSession()->getBag('members_session');

        $data = $sessionBag->get('oauth_state_data');

        $parameter = $data['parameter'] ?? [];
        $targetUrl = !empty($parameter['target_path']) ? $parameter['target_path'] : '/';

        $sessionBag->remove('oauth_state_data');

        $request->attributes->set('user', $token->getUser());

        return new RedirectResponse($targetUrl);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $session = $request->getSession();

        /** @var AttributeBagInterface $sessionBag */
        $sessionBag = $session->getBag('members_session');

        $data = $sessionBag->get('oauth_state_data');

        $parameter = $data['parameter'] ?? [];
        $parameterLocale = $parameter['locale'] ?? null;

        $sessionBag->remove('oauth_state_data');
        $session->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);

        if ($parameterLocale !== null) {
            $request->setLocale($parameterLocale);
        }

        $parameter = [];
        $routeName = 'members_user_security_login';

        if ($exception instanceof AccountNotLinkedException) {
            $parameter = ['registrationKey' => $exception->getRegistrationKey()];
            $routeName = 'members_user_registration_register';
        }

        return new RedirectResponse($this->router->generate($routeName, $parameter), Response::HTTP_TEMPORARY_REDIRECT);
    }

    private function getClient(string $service): OAuth2ClientInterface
    {
        return $this->clientRegistry->getClient($service);
    }
}
