<?php

namespace MembersBundle\Security\OAuth;

use MembersBundle\Adapter\Sso\SsoIdentityInterface;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Manager\SsoIdentityManagerInterface;
use MembersBundle\Manager\UserManagerInterface;
use MembersBundle\Service\RequestPropertiesForUserExtractorServiceInterface;

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
     * @var RequestPropertiesForUserExtractorServiceInterface
     */
    protected $requestPropertiesForUserExtractorService;

    /**
     * @param SsoIdentityManagerInterface                       $ssoIdentityManager
     * @param AccountConnectorInterface                         $accountConnector
     * @param UserManagerInterface                              $userManager
     * @param RequestPropertiesForUserExtractorServiceInterface $requestPropertiesForUserExtractorService
     */
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
        $newUserIdentityKey = sprintf('sso-%s', \Ramsey\Uuid\Uuid::uuid4()->toString());

        $user = $this->userManager->createAnonymousUser($newUserIdentityKey);

        $parameters = $oAuthResponse->getParameter();

        $user->setPublished(true);

        // persist user first before creating sso identity
        $this->userManager->updateUser($user, $this->requestPropertiesForUserExtractorService->extractFromParameterBag($parameters));

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
