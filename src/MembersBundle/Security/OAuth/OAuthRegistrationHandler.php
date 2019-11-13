<?php

namespace MembersBundle\Security\OAuth;

use MembersBundle\Adapter\Sso\SsoIdentityInterface;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Manager\SsoIdentityManagerInterface;
use MembersBundle\Manager\UserManagerInterface;

class OAuthRegistrationHandler
{
    /**
     * @var SsoIdentityManagerInterface
     */
    protected $ssoIdentityManager;

    /**
     * @var AccountConnectorInterface
     */
    protected $accountConnector;

    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * @param SsoIdentityManagerInterface $ssoIdentityManager
     * @param AccountConnectorInterface   $accountConnector
     * @param UserManagerInterface        $userManager
     */
    public function __construct(
        SsoIdentityManagerInterface $ssoIdentityManager,
        AccountConnectorInterface $accountConnector,
        UserManagerInterface $userManager
    ) {
        $this->ssoIdentityManager = $ssoIdentityManager;
        $this->accountConnector = $accountConnector;
        $this->userManager = $userManager;
    }

    /**
     * @param OAuthResponseInterface $OAuthResponse
     *
     * @return UserInterface|null
     */
    public function getUserFromUserResponse(OAuthResponseInterface $OAuthResponse)
    {
        return $this->ssoIdentityManager->getUserBySsoIdentity($OAuthResponse->getProvider(), $OAuthResponse->getResourceOwner()->getId());
    }

    /**
     * @param OAuthResponseInterface $oAuthResponse
     *
     * @return UserInterface
     *
     * @throws \Exception
     */
    public function connectNewUserWithSsoIdentity(OAuthResponseInterface $oAuthResponse)
    {
        /** @var UserInterface $user */
        $user = $this->userManager->createUser();

        $user->setEmail($oAuthResponse->getResourceOwner()->getId());
        $user->setPublished(true);

        // persist user first before creating sso identity
        $this->userManager->updateUser($user);

        $this->connectSsoIdentity($user, $oAuthResponse);

        return $user;
    }

    /**
     * @param UserInterface          $user
     * @param OAuthResponseInterface $oAuthResponse
     *
     * @return SsoIdentityInterface
     *
     * @throws \Exception
     */
    public function connectSsoIdentity(UserInterface $user, OAuthResponseInterface $oAuthResponse)
    {
        if (!$user->getId()) {
            throw new \LogicException('Can\'t add a SSO identity to a user which is not saved. Please save user first');
        }

        $ssoIdentity = $this->accountConnector->connectToSsoIdentity($user, $oAuthResponse);

        $this->ssoIdentityManager->saveIdentity($ssoIdentity);
        $this->userManager->updateUser($user);

        return $ssoIdentity;
    }
}
