<?php

namespace MembersBundle\Twig\Extension;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use MembersBundle\Adapter\Sso\SsoIdentityInterface;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Security\OAuth\SsoIdentity\SsoIdentityServiceInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class OAuthExtension extends AbstractExtension
{
    /**
     * @var bool
     */
    protected $oauthEnabled;

    /**
     * @var ClientRegistry
     */
    protected $oauthRegistry;

    /**
     * @var SsoIdentityServiceInterface
     */
    protected $identityService;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @param bool                        $oauthEnabled
     * @param ClientRegistry              $oauthRegistry
     * @param SsoIdentityServiceInterface $identityService
     * @param TokenStorageInterface       $tokenStorage
     */
    public function __construct(
        bool $oauthEnabled,
        ClientRegistry $oauthRegistry,
        SsoIdentityServiceInterface $identityService,
        TokenStorageInterface $tokenStorage
    ) {
        $this->oauthEnabled = $oauthEnabled;
        $this->oauthRegistry = $oauthRegistry;
        $this->identityService = $identityService;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('members_oauth_enabled', [$this, 'oauthIsEnabled']),
            new TwigFunction('members_oauth_social_links', [$this, 'getSocialLinks'])
        ];
    }

    /**
     * @return bool
     */
    public function oauthIsEnabled()
    {
        return $this->oauthEnabled;
    }

    /**
     * @param string $route members_user_security_oauth_login|members_user_security_oauth_connect
     * @param bool   $skipConnectedIdentities
     *
     * @return array
     */
    public function getSocialLinks(string $route = 'members_user_security_oauth_login', $skipConnectedIdentities = false)
    {
        if ($this->oauthIsEnabled() === false) {
            return [];
        }

        $processType = $route === 'members_user_security_oauth_connect' ? 'connect' : 'login';

        $ssoIdentities = $this->getSsoIdentities();
        $resourceOwners = $this->oauthRegistry->getEnabledClientKeys();

        if (!is_array($resourceOwners)) {
            return [];
        }

        $data = [];

        foreach ($resourceOwners as $resourceOwner) {

            if ($skipConnectedIdentities === true && in_array($resourceOwner, $ssoIdentities)) {
                continue;
            }

            $data[] = [
                'route_name'   => $route,
                'process_type' => $processType,
                'identifier'   => $resourceOwner,
                'connected'    => in_array($resourceOwner, $ssoIdentities),
            ];
        }

        return $data;
    }

    /**
     * @return array|SsoIdentityInterface[]
     */
    protected function getSsoIdentities()
    {
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;

        if (!$user instanceof UserInterface) {
            return [];
        }

        return array_map(function (SsoIdentityInterface $identity) {
            return $identity->getProvider();
        }, $this->identityService->getSsoIdentities($user));
    }

}
