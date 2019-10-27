<?php

namespace MembersBundle\Security\OAuth\SsoIdentity;

use MembersBundle\Adapter\Sso\SsoIdentityInterface;
use MembersBundle\Adapter\User\UserInterface;

interface SsoIdentityServiceInterface
{
    /**
     * @param UserInterface $user
     *
     * @return SsoIdentityInterface[]
     */
    public function getSsoIdentities(UserInterface $user);

    /**
     * @param string $provider
     * @param string $identifier
     *
     * @return UserInterface|null
     */
    public function getCustomerBySsoIdentity(string $provider, $identifier);

    /**
     * @param UserInterface $user
     * @param string        $provider
     * @param string        $identifier
     *
     * @return SsoIdentityInterface|null
     */
    public function getSsoIdentity(UserInterface $user, $provider, $identifier);

    /**
     * @param UserInterface        $user
     * @param SsoIdentityInterface $ssoIdentity
     *
     * @return $this
     */
    public function addSsoIdentity(UserInterface $user, SsoIdentityInterface $ssoIdentity);

    /**
     * @param UserInterface $user
     * @param string        $provider
     * @param string        $identifier
     * @param mixed         $profileData
     *
     * @return SsoIdentityInterface
     */
    public function createSsoIdentity(UserInterface $user, $provider, $identifier, $profileData);
}