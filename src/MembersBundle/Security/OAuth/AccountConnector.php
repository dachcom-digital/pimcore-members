<?php

namespace MembersBundle\Security\OAuth;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use MembersBundle\Adapter\Sso\SsoIdentityInterface;
use MembersBundle\Adapter\User\UserInterface as MembersUserInterface;
use MembersBundle\Security\OAuth\SsoIdentity\SsoIdentityServiceInterface;
use Pimcore\Model\DataObject\SsoIdentity;
use Symfony\Component\Security\Core\User\UserInterface;

class AccountConnector implements AccountConnectorInterface
{
    /**
     * @var SsoIdentityServiceInterface
     */
    protected $ssoIdentityService;

    /**
     * @param SsoIdentityServiceInterface $ssoIdentityService
     */
    public function __construct(SsoIdentityServiceInterface $ssoIdentityService)
    {
        $this->ssoIdentityService = $ssoIdentityService;
    }

    /**
     * @param UserInterface          $user
     * @param string                 $provider
     * @param ResourceOwnerInterface $resourceOwner
     */
    public function connect(UserInterface $user, string $provider, ResourceOwnerInterface $resourceOwner)
    {
        $this->connectToSsoIdentity($user, $provider, $resourceOwner);
    }

    /**
     * @param UserInterface          $user
     * @param OAuthResponseInterface $oAuthResponse
     *
     * @return SsoIdentityInterface|SsoIdentity|null
     */
    public function connectToSsoIdentity(UserInterface $user, OAuthResponseInterface $oAuthResponse)
    {
        if (!$user instanceof MembersUserInterface) {
            throw new \InvalidArgumentException('User is not supported');
        }

        $identifier = $oAuthResponse->getResourceOwner()->getId();
        $ssoIdentity = $this->ssoIdentityService->getSsoIdentity($user, $oAuthResponse->getProvider(), $identifier);

        if ($ssoIdentity !== null) {
            throw new \RuntimeException(
                sprintf(
                    'Customer has already an SSO identity for provider %s and identifier %s',
                    $oAuthResponse->getProvider(),
                    $identifier
                )
            );
        }

        $ssoIdentity = $this->ssoIdentityService->createSsoIdentity(
            $user,
            $oAuthResponse->getProvider(),
            $identifier,
            json_encode($oAuthResponse->getResourceOwner()->toArray())
        );

        $this->applyCredentialsToSsoIdentity($ssoIdentity, $oAuthResponse);
        $this->applyProfileToCustomer($user, $oAuthResponse);

        $this->ssoIdentityService->addSsoIdentity($user, $ssoIdentity);

        return $ssoIdentity;
    }

    /**
     * @param SsoIdentityInterface|SsoIdentity $ssoIdentity
     * @param OAuthResponseInterface           $oAuthResponse
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
     */
    protected function applyProfileToCustomer(MembersUserInterface $user, OAuthResponseInterface $oAuthResponse)
    {
        //@fixme: add dynamic mapping

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
