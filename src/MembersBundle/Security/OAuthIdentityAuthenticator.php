<?php

namespace MembersBundle\Security;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2Client;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Manager\SsoIdentityManagerInterface;
use MembersBundle\Security\OAuth\Dispatcher\Router\DispatchRouter;
use MembersBundle\Security\OAuth\Exception\AccountNotLinkedException;
use MembersBundle\Security\OAuth\OAuthResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class OAuthIdentityAuthenticator extends SocialAuthenticator
{
    /**
     * @var UrlGeneratorInterface
     */
    protected $router;

    /**
     * @var ClientRegistry
     */
    protected $clientRegistry;

    /**
     * @var SsoIdentityManagerInterface
     */
    protected $ssoIdentityManager;

    /**
     * @var DispatchRouter
     */
    protected $dispatchRouter;

    /**
     * @param UrlGeneratorInterface       $router
     * @param ClientRegistry              $clientRegistry
     * @param SsoIdentityManagerInterface $ssoIdentityManager
     * @param DispatchRouter              $dispatchRouter
     */
    public function __construct(
        UrlGeneratorInterface $router,
        ClientRegistry $clientRegistry,
        SsoIdentityManagerInterface $ssoIdentityManager,
        DispatchRouter $dispatchRouter
    ) {
        $this->router = $router;
        $this->clientRegistry = $clientRegistry;
        $this->ssoIdentityManager = $ssoIdentityManager;
        $this->dispatchRouter = $dispatchRouter;
    }

    /**
     * @param Request $request
     *
     * @return array|bool|string
     */
    public function supports(Request $request)
    {
        return $request->attributes->get('_route') === 'members_user_security_oauth_check';
    }

    /**
     * @param Request $request
     *
     * @return array|null
     */
    public function getCredentials(Request $request)
    {
        /** @var NamespacedAttributeBag $sessionBag */
        $sessionBag = $request->getSession()->getBag('members_session');

        if (!$sessionBag->has('oauth_state_data')) {
            throw new CustomUserMessageAuthenticationException(
                'No oauth_state_data given.'
            );
        }

        $data = $sessionBag->get('oauth_state_data');
        $accessToken = $this->fetchAccessToken($this->getClient($data['provider']));

        return [
            'access_token'     => $accessToken,
            'oauth_state_data' => $data
        ];
    }

    /**
     * @param mixed                 $credentials
     * @param UserProviderInterface $userProvider
     *
     * @return UserInterface
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if (!is_array($credentials)) {
            throw new CustomUserMessageAuthenticationException(
                'credentials needs to be a array.'
            );
        }

        if (!isset($credentials['oauth_state_data'])) {
            throw new CustomUserMessageAuthenticationException(
                'No oauth_state_data given.'
            );
        }

        if (!isset($credentials['access_token'])) {
            throw new CustomUserMessageAuthenticationException(
                'No token given.'
            );
        }

        $data = $credentials['oauth_state_data'];

        $provider = $data['provider'];
        $type = $data['type'];
        $parameter = $data['parameter'] ?? [];

        /** @var AccessToken $accessToken */
        $accessToken = $credentials['access_token'];

        /** @var ResourceOwnerInterface $user */
        $user = $this->getClient($provider)->fetchUserFromToken($accessToken);

        $oAuthResponse = new OAuthResponse($provider, $accessToken, $user, $parameter);

        $memberUser = $this->ssoIdentityManager->getUserBySsoIdentity($oAuthResponse->getProvider(), $oAuthResponse->getResourceOwner()->getId());

        if ($memberUser instanceof UserInterface) {
            return $memberUser;
        }

        return $this->dispatchRouter->dispatch($type, $provider, $oAuthResponse);
    }

    /**
     * @param Request        $request
     * @param TokenInterface $token
     * @param string         $providerKey
     *
     * @return RedirectResponse|Response|null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        /** @var NamespacedAttributeBag $sessionBag */
        $sessionBag = $request->getSession()->getBag('members_session');

        $data = $sessionBag->get('oauth_state_data');

        $parameter = $data['parameter'] ?? [];

        $targetUrl = isset($parameter['target_path']) && !empty($parameter['target_path']) ? $parameter['target_path'] : '/';

        $sessionBag->remove('oauth_state_data');

        $request->attributes->set('user', $token->getUser());

        return new RedirectResponse($targetUrl);
    }

    /**
     * @param Request                 $request
     * @param AuthenticationException $exception
     *
     * @return Response
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $session = $request->getSession();

        /** @var NamespacedAttributeBag $sessionBag */
        $sessionBag = $session->getBag('members_session');

        $data = $sessionBag->get('oauth_state_data');

        $parameter = $data['parameter'] ?? [];
        $parameterLocale = $parameter['locale'] ?? null;

        $sessionBag->remove('oauth_state_data');
        $session->set(Security::AUTHENTICATION_ERROR, $exception);

        if ($request->getLocale() === null && $parameterLocale !== null) {
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

    /**
     * @param Request                      $request
     * @param AuthenticationException|null $authException
     *
     * @return RedirectResponse
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse(
            $this->router->generate('members_user_security_login'),
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }

    /**
     * @param string $service
     *
     * @return OAuth2Client
     */
    private function getClient(string $service)
    {
        return $this->clientRegistry->getClient($service);
    }
}
