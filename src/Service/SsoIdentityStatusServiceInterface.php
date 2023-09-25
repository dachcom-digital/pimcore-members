<?php

namespace MembersBundle\Service;

use MembersBundle\Adapter\User\UserInterface;

interface SsoIdentityStatusServiceInterface
{
    public function identityCanCompleteProfile(UserInterface $user): bool;

    public function identityCanBeDeleted(UserInterface $user): bool;
}
