<?php

namespace MembersBundle\Security\OAuth;

use MembersBundle\Adapter\Sso\SsoIdentityInterface;
use MembersBundle\Adapter\User\UserInterface as MembersUserInterface;
use MembersBundle\Manager\SsoIdentityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AccountConnector implements AccountConnectorInterface
{
    /**
     * @var SsoIdentityManagerInterface
     */
    protected $ssoIdentityManager;

    /**
     * @param SsoIdentityManagerInterface $ssoIdentityManager
     */
    public function __construct(SsoIdentityManagerInterface $ssoIdentityManager)
    {
        $this->ssoIdentityManager = $ssoIdentityManager;
    }

    /**
     * @param UserInterface          $user
     * @param OAuthResponseInterface $oAuthResponse
     *
     * @return SsoIdentityInterface
     */
    public function connectToSsoIdentity(UserInterface $user, OAuthResponseInterface $oAuthResponse)
    {
        if (!$user instanceof MembersUserInterface) {
            throw new \InvalidArgumentException('User is not supported');
        }

        $identifier = $oAuthResponse->getResourceOwner()->getId();
        $ssoIdentity = $this->ssoIdentityManager->getSsoIdentity($user, $oAuthResponse->getProvider(), $identifier);

        if ($ssoIdentity !== null) {
            throw new \RuntimeException(
                sprintf(
                    'User has already an SSO identity for provider %s and identifier %s',
                    $oAuthResponse->getProvider(),
                    $identifier
                )
            );
        }

        $ssoIdentity = $this->ssoIdentityManager->createSsoIdentity(
            $user,
            $oAuthResponse->getProvider(),
            $identifier,
            json_encode($oAuthResponse->getResourceOwner()->toArray())
        );

        $this->applyCredentialsToSsoIdentity($ssoIdentity, $oAuthResponse);
        $this->applyProfileToUser($user, $oAuthResponse);

        $this->ssoIdentityManager->addSsoIdentity($user, $ssoIdentity);

        return $ssoIdentity;
    }

    /**
     * @param SsoIdentityInterface   $ssoIdentity
     * @param OAuthResponseInterface $oAuthResponse
     */
    protected function applyCredentialsToSsoIdentity(SsoIdentityInterface $ssoIdentity, OAuthResponseInterface $oAuthResponse)
    {
        $token = $oAuthResponse->getAccessToken();
        $tokenValues = $token->getValues();

        $ssoIdentity->setAccessToken($token->getToken());
        $ssoIdentity->setRefreshToken($token->getRefreshToken());
        $ssoIdentity->setExpiresAt($token->getExpires());

        if (empty($ssoIdentity->getScope())) {
            $scope = isset($tokenValues['scope']) ? $tokenValues['scope'] : null;
            $ssoIdentity->setScope($scope);
        }

        if (empty($ssoIdentity->getTokenType())) {
            $scope = isset($tokenValues['token_type']) ? $tokenValues['token_type'] : null;
            $ssoIdentity->setTokenType($scope);
        }
    }

    /**
     * @param MembersUserInterface   $user
     * @param OAuthResponseInterface $oAuthResponse
     *
     * @todo: move to resource mapping service
     */
    protected function applyProfileToUser(MembersUserInterface $user, OAuthResponseInterface $oAuthResponse)
    {
        $ownerDetails = $oAuthResponse->getResourceOwner()->toArray();
        foreach ($ownerDetails as $property => $value) {
            $this->setIfEmpty($user, $property, $value);
        }
    }

    /**
     * @param MembersUserInterface $user
     * @param string               $property
     * @param mixed                $value
     */
    protected function setIfEmpty(MembersUserInterface $user, $property, $value = null)
    {
        $getter = 'get' . ucfirst($property);
        $setter = 'set' . ucfirst($property);

        if (!method_exists($user, $getter)) {
            return;
        }

        if (!method_exists($user, $setter)) {
            return;
        }

        if (!empty($value) && empty($user->$getter())) {
            $user->$setter($value);
        }
    }
}
