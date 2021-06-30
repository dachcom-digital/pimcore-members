<?php

namespace MembersBundle\Security\OAuth;

use MembersBundle\Adapter\Sso\SsoIdentityInterface;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Manager\SsoIdentityManagerInterface;
use MembersBundle\Manager\UserManagerInterface;
use MembersBundle\Service\RequestPropertiesForUserExtractorServiceInterface;
use Symfony\Component\Uid\Uuid;

class OAuthRegistrationHandler
{
    protected SsoIdentityManagerInterface $ssoIdentityManager;
    protected AccountConnectorInterface $accountConnector;
    protected UserManagerInterface $userManager;
    protected RequestPropertiesForUserExtractorServiceInterface $requestPropertiesForUserExtractorService;

    public function __construct(
        SsoIdentityManagerInterface $ssoIdentityManager,
        AccountConnectorInterface $accountConnector,
        UserManagerInterface $userManager,
        RequestPropertiesForUserExtractorServiceInterface $requestPropertiesForUserExtractorService
    ) {
        $this->ssoIdentityManager = $ssoIdentityManager;
        $this->accountConnector = $accountConnector;
        $this->userManager = $userManager;
        $this->requestPropertiesForUserExtractorService = $requestPropertiesForUserExtractorService;
    }

    public function getUserFromUserResponse(OAuthResponseInterface $OAuthResponse): ?UserInterface
    {
        return $this->ssoIdentityManager->getUserBySsoIdentity($OAuthResponse->getProvider(), $OAuthResponse->getResourceOwner()->getId());
    }

    public function connectNewUserWithSsoIdentity(OAuthResponseInterface $oAuthResponse): UserInterface
    {
        $newUserIdentityKey = sprintf('sso-%s', Uuid::v4()->toRfc4122());

        $user = $this->userManager->createAnonymousUser($newUserIdentityKey);

        $parameters = $oAuthResponse->getParameter();

        $user->setPublished(true);

        // persist user first before creating sso identity
        $this->userManager->updateUser($user, $this->requestPropertiesForUserExtractorService->extractFromParameterBag($parameters));

        $this->connectSsoIdentity($user, $oAuthResponse);

        return $user;
    }

    public function connectSsoIdentity(UserInterface $user, OAuthResponseInterface $oAuthResponse): SsoIdentityInterface
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
