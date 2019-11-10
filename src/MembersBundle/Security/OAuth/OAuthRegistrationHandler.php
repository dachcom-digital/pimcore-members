<?php

namespace MembersBundle\Security\OAuth;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use MembersBundle\Adapter\Sso\SsoIdentityInterface;
use MembersBundle\Security\OAuth\SsoIdentity\SsoIdentityServiceInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class OAuthRegistrationHandler
{
    /**
     * @var ClientRegistry
     */
    protected $oauthRegistry;

    /**
     * @var SsoIdentityServiceInterface
     */
    protected $ssoIdentityService;

    /**
     * @var AccountConnectorInterface
     */
    protected $accountConnector;

    /**
     * @param ClientRegistry              $oauthRegistry
     * @param SsoIdentityServiceInterface $ssoIdentityService
     * @param AccountConnectorInterface   $accountConnector
     */
    public function __construct(
        ClientRegistry $oauthRegistry,
        SsoIdentityServiceInterface $ssoIdentityService,
        AccountConnectorInterface $accountConnector
    ) {
        $this->oauthRegistry = $oauthRegistry;
        $this->ssoIdentityService = $ssoIdentityService;
        $this->accountConnector = $accountConnector;
    }

    /**
     * @param OAuthResponseInterface $OAuthResponse
     *
     * @return UserInterface|null
     */
    public function getCustomerFromUserResponse(OAuthResponseInterface $OAuthResponse)
    {
        return $this->ssoIdentityService->getCustomerBySsoIdentity($OAuthResponse->getProvider(), $OAuthResponse->getResourceOwner()->getId());
    }

    /**
     * @param UserInterface          $user
     * @param OAuthResponseInterface $oAuthResponse
     *
     * @return SsoIdentityInterface
     * @throws \Exception
     */
    public function connectSsoIdentity(UserInterface $user, OAuthResponseInterface $oAuthResponse)
    {
        if (!$user->getId()) {
            throw new \LogicException('Can\'t add a SSO identity to a customer which is not saved. Please save user first');
        }

        $ssoIdentity = $this->accountConnector->connectToSsoIdentity($user, $oAuthResponse);

        // the connector does not save the customer and the identity
        $ssoIdentity->save();
        $user->save();

        return $ssoIdentity;
    }
}
