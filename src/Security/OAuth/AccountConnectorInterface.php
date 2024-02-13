<?php

namespace MembersBundle\Security\OAuth;

use MembersBundle\Adapter\Sso\SsoIdentityInterface;
use Symfony\Component\Security\Core\User\UserInterface;

interface AccountConnectorInterface
{
    public function connectToSsoIdentity(UserInterface $user, OAuthResponseInterface $oAuthResponse): SsoIdentityInterface;

    public function refreshSsoIdentityUser(UserInterface $user, OAuthResponseInterface $oAuthResponse): void;
}
