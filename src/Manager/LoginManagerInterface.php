<?php

namespace MembersBundle\Manager;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

interface LoginManagerInterface
{
    public function logInUser(string $firewallName, UserInterface $user, Response $response = null): void;
}
