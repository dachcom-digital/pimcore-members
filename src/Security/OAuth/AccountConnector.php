<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace MembersBundle\Security\OAuth;

use MembersBundle\Adapter\Sso\SsoIdentityInterface;
use MembersBundle\Adapter\User\UserInterface as MembersUserInterface;
use MembersBundle\Exception\EntityNotRefreshedException;
use MembersBundle\Manager\SsoIdentityManagerInterface;
use MembersBundle\Service\ResourceMappingService;
use Symfony\Component\Security\Core\User\UserInterface;

class AccountConnector implements AccountConnectorInterface
{
    public function __construct(
        protected SsoIdentityManagerInterface $ssoIdentityManager,
        protected ResourceMappingService $resourceMappingService
    ) {
    }

    public function connectToSsoIdentity(UserInterface $user, OAuthResponseInterface $oAuthResponse): SsoIdentityInterface
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

        try {
            $this->resourceMappingService->mapResourceData($user, $oAuthResponse->getResourceOwner(), ResourceMappingService::MAP_FOR_PROFILE);
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException($e->getMessage());
        }

        $this->ssoIdentityManager->addSsoIdentity($user, $ssoIdentity);

        return $ssoIdentity;
    }

    /**
     * @throws EntityNotRefreshedException
     */
    public function refreshSsoIdentityUser(UserInterface $user, OAuthResponseInterface $oAuthResponse): void
    {
        if (!$user instanceof MembersUserInterface) {
            throw new \InvalidArgumentException('User is not supported');
        }

        $this->resourceMappingService->mapResourceData($user, $oAuthResponse->getResourceOwner(), ResourceMappingService::MAP_FOR_REFRESH);
    }

    protected function applyCredentialsToSsoIdentity(SsoIdentityInterface $ssoIdentity, OAuthResponseInterface $oAuthResponse): void
    {
        $token = $oAuthResponse->getAccessToken();
        $tokenValues = $token->getValues();

        $ssoIdentity->setAccessToken($token->getToken());
        $ssoIdentity->setRefreshToken($token->getRefreshToken());
        $ssoIdentity->setExpiresAt($token->getExpires());

        if (empty($ssoIdentity->getScope())) {
            $scope = $tokenValues['scope'] ?? null;
            $ssoIdentity->setScope($scope);
        }

        if (empty($ssoIdentity->getTokenType())) {
            $scope = $tokenValues['token_type'] ?? null;
            $ssoIdentity->setTokenType($scope);
        }
    }
}
