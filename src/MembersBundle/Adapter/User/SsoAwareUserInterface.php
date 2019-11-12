<?php

namespace MembersBundle\Adapter\User;

use MembersBundle\Adapter\Sso\SsoIdentityInterface;

interface SsoAwareUserInterface
{
    /**
     * @return SsoIdentityInterface[]
     */
    public function getSsoIdentities();

    /**
     * @param SsoIdentityInterface[] $ssoIdentities
     */
    public function setSsoIdentities($ssoIdentities);
}
