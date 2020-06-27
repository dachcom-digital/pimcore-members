<?php

namespace MembersBundle\Service;

use MembersBundle\Adapter\User\UserInterface;

interface SsoIdentityStatusServiceInterface
{
    /**
     * @param UserInterface $user
     *
     * @return bool
     */
    public function identityCanCompleteProfile(UserInterface $user);

    /**
     * @param UserInterface $user
     *
     * @return bool
     */
    public function identityCanBeDeleted(UserInterface $user);
}
