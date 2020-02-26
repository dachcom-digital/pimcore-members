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
    /**
     * @var ClientRegistry
     */
    protected $oauthRegistry;

    /**
     * @var SsoIdentityManagerInterface
     */
    protected $ssoIdentityManager;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var SsoIdentityStatusServiceInterface
     */
    protected $identityStatusService;

    /**
     * @param ClientRegistry                    $oauthRegistry
     * @param SsoIdentityManagerInterface       $ssoIdentityManager
     * @param TokenStorageInterface             $tokenStorage
     * @param SsoIdentityStatusServiceInterface $identityStatusService
     */
    public function __construct(
        ClientRegistry $oauthRegistry,
        SsoIdentityManagerInterface $ssoIdentityManager,
        TokenStorageInterface $tokenStorage,
        SsoIdentityStatusServiceInterface $identityStatusService
    ) {
        $this->oauthRegistry = $oauthRegistry;
        $this->ssoIdentityManager = $ssoIdentityManager;
        $this->tokenStorage = $tokenStorage;
        $this->identityStatusService = $identityStatusService;
    }

    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('members_oauth_social_links', [$this, 'getSocialLinks']),
            new TwigFunction('members_oauth_can_complete_profile', [$this, 'canCompleteProfile'])
        ];
    }

    /**
     * @return bool
     */
    public function canCompleteProfile()
    {
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;

        if (!$user instanceof UserInterface) {
            return false;
        }

        return $this->identityStatusService->identityCanCompleteProfile($user);
    }

    /**
     * @param string $route members_user_security_oauth_login|members_user_security_oauth_connect
     * @param bool   $skipConnectedIdentities
     *
     * @return array
     */
    public function getSocialLinks(string $route = 'members_user_security_oauth_login', $skipConnectedIdentities = false)
    {
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
        }, $this->ssoIdentityManager->getSsoIdentities($user));
    }
}
