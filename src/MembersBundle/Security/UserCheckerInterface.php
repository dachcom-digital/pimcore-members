<?php

namespace MembersBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;

interface UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user);

    public function checkPostAuth(UserInterface $user);
}
