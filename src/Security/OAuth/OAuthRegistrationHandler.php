<?php

namespace MembersBundle\Security\OAuth;

use MembersBundle\Adapter\Sso\SsoIdentityInterface;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Exception\EntityNotRefreshedException;
use MembersBundle\Manager\SsoIdentityManagerInterface;
use MembersBundle\Manager\UserManagerInterface;
use MembersBundle\Service\RequestPropertiesForUserExtractorServiceInterface;
use Symfony\Component\Uid\Uuid;

class OAuthRegistrationHandler
{
    public function __construct(
        protected SsoIdentityManagerInterface $ssoIdentityManager,
        protected AccountConnectorInterface $accountConnector,
        protected UserManagerInterface $userManager,
        protected RequestPropertiesForUserExtractorServiceInterface $requestPropertiesForUserExtractorService
    ) {
    }

    public function getUserFromUserResponse(OAuthResponseInterface $OAuthResponse): ?UserInterface
    {
        return $this->ssoIdentityManager->getUserBySsoIdentity($OAuthResponse->getProvider(), $OAuthResponse->getResourceOwner()->getId());
    }

    public function getRefreshedUserFromUserResponse(OAuthResponseInterface $oAuthResponse): ?UserInterface
    {
        $user = $this->ssoIdentityManager->getUserBySsoIdentity($oAuthResponse->getProvider(), $oAuthResponse->getResourceOwner()->getId());

        if (!$user instanceof UserInterface) {
            return null;
        }

        try {
            $this->accountConnector->refreshSsoIdentityUser($user, $oAuthResponse);
        } catch (EntityNotRefreshedException $e) {
            // entity hasn't changed. return
            return $user;
        }

        $this->userManager->updateUser($user);

        return $user;
    }

    /**
     * @throws \Exception
     */
    public function connectNewUserWithSsoIdentity(OAuthResponseInterface $oAuthResponse): UserInterface
    {
        $newUserIdentityKey = sprintf('sso-%s', (Uuid::v4())->toRfc4122());

        $user = $this->userManager->createAnonymousUser($newUserIdentityKey);

        $parameters = $oAuthResponse->getParameter();

        $user->setPublished(true);

        // persist user first before creating sso identity
        $this->userManager->updateUser($user, $this->requestPropertiesForUserExtractorService->extractFromParameterBag($parameters));

        $this->connectSsoIdentity($user, $oAuthResponse);

        return $user;
    }

    /**
     * @throws \Exception
     */
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
