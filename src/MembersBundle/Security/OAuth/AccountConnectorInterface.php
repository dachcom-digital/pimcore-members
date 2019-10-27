<?php

namespace MembersBundle\Security\OAuth;

use MembersBundle\Adapter\Sso\SsoIdentityInterface;
use Pimcore\Model\DataObject\SsoIdentity;
use Symfony\Component\Security\Core\User\UserInterface;

interface AccountConnectorInterface
{
    /**
     * @param UserInterface          $user
     * @param OAuthResponseInterface $oAuthResponse
     *
     * @return SsoIdentityInterface|SsoIdentity
     */
    public function connectToSsoIdentity(UserInterface $user, OAuthResponseInterface $oAuthResponse);
}
