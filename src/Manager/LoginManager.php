<?php

namespace MembersBundle\Manager;

use MembersBundle\Security\UserChecker;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\RememberMe\RememberMeHandlerInterface;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;

class LoginManager implements LoginManagerInterface
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private UserChecker $userChecker,
        private SessionAuthenticationStrategyInterface $sessionStrategy,
        private RequestStack $requestStack,
        private ?RememberMeHandlerInterface $rememberMeService = null
    ) {
    }

    final public function logInUser(string $firewallName, UserInterface $user, Response $response = null): void
    {
        $this->userChecker->checkPreAuth($user);

        $token = $this->createToken($firewallName, $user);
        $request = $this->requestStack->getCurrentRequest();

        if (null !== $request) {
            $this->sessionStrategy->onAuthentication($request, $token);

            if (null !== $response && null !== $this->rememberMeService) {
                $this->rememberMeService->createRememberMeCookie($user);
            }
        }

        $this->tokenStorage->setToken($token);
    }

    protected function createToken(string $firewall, UserInterface $user): UsernamePasswordToken
    {
        return new UsernamePasswordToken($user, $firewall, $user->getRoles());
    }
}
