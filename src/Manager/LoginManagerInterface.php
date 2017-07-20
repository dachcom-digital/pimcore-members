<?php

namespace MembersBundle\Manager;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

interface LoginManagerInterface
{
    /**
     * @param string          $firewallName
     * @param UserInterface $user
     * @param Response|null   $response
     */
    public function logInUser($firewallName, UserInterface $user, Response $response = NULL);
}
