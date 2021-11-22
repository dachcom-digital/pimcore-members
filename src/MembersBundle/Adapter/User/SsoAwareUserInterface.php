<?php

namespace MembersBundle\Adapter\User;

use MembersBundle\Adapter\Sso\SsoIdentityInterface;

interface SsoAwareUserInterface
{
    /**
     * @return array<int, SsoIdentityInterface>
     */
    public function getSsoIdentities(): array;

    /**
     * @param array|null $ssoIdentities
     *
     * @return $this
     */
    public function setSsoIdentities(?array $ssoIdentities);
}
