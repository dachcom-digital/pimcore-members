<?php

namespace MembersBundle\Twig\Extension;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use MembersBundle\Adapter\Sso\SsoIdentityInterface;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Manager\SsoIdentityManagerInterface;
use MembersBundle\Service\SsoIdentityStatusServiceInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class OAuthExtension extends AbstractExtension
{
    public function __construct(
        protected ClientRegistry $oauthRegistry,
        protected SsoIdentityManagerInterface $ssoIdentityManager,
        protected TokenStorageInterface $tokenStorage,
        protected SsoIdentityStatusServiceInterface $identityStatusService
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('members_oauth_social_links', [$this, 'getSocialLinks']),
            new TwigFunction('members_oauth_can_complete_profile', [$this, 'canCompleteProfile'])
        ];
    }

    public function canCompleteProfile(): bool
    {
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;

        if (!$user instanceof UserInterface) {
            return false;
        }

        return $this->identityStatusService->identityCanCompleteProfile($user);
    }

    /**
     * Allowed route parameters: members_user_security_oauth_login|members_user_security_oauth_connect
     */
    public function getSocialLinks(string $route = 'members_user_security_oauth_login', bool $skipConnectedIdentities = false): array
    {
        $processType = $route === 'members_user_security_oauth_connect' ? 'connect' : 'login';

        $ssoIdentityProviders = $this->getSsoIdentityProvider();
        $resourceOwners = $this->oauthRegistry->getEnabledClientKeys();

        $data = [];

        foreach ($resourceOwners as $resourceOwner) {
            if ($skipConnectedIdentities === true && in_array($resourceOwner, $ssoIdentityProviders, true)) {
                continue;
            }

            $data[] = [
                'route_name'   => $route,
                'process_type' => $processType,
                'identifier'   => $resourceOwner,
                'connected'    => in_array($resourceOwner, $ssoIdentityProviders, true),
            ];
        }

        return $data;
    }

    protected function getSsoIdentityProvider(): array
    {
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;

        if (!$user instanceof UserInterface) {
            return [];
        }

        return array_map(static function (SsoIdentityInterface $identity) {
            return $identity->getProvider();
        }, $this->ssoIdentityManager->getSsoIdentities($user));
    }
}
