<?php

namespace DachcomBundle\Test\Unit\Manager;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Manager\LoginManager;
use MembersBundle\Security\UserChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\RememberMe\RememberMeHandlerInterface;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;

class LoginManagerTest extends TestCase
{
    public function testLogInUserWithRequestStack(): void
    {
        $loginManager = $this->createLoginManager();
        $loginManager->logInUser('main', $this->mockUser());
    }

    public function testLogInUserWithRememberMeAndRequestStack(): void
    {
        $response = $this->getMockBuilder(Response::class)->getMock();
        $loginManager = $this->createLoginManager($response);
        $loginManager->logInUser('main', $this->mockUser(), $response);
    }

    private function createLoginManager(Response $response = null)
    {
        $tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $tokenStorage
            ->expects($this->once())
            ->method('setToken')
            ->with($this->isInstanceOf(TokenInterface::class));

        $userChecker = $this->getMockBuilder(UserChecker::class)->getMock();
        $userChecker
            ->expects($this->once())
            ->method('checkPreAuth')
            ->with($this->isInstanceOf(UserInterface::class));

        $request = $this->getMockBuilder(Request::class)->getMock();

        $sessionStrategy = $this->getMockBuilder(SessionAuthenticationStrategyInterface::class)->getMock();
        $sessionStrategy
            ->expects($this->once())
            ->method('onAuthentication')
            ->with($request, $this->isInstanceOf(TokenInterface::class));

        $requestStack = $this->getMockBuilder(RequestStack::class)->getMock();
        $requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $rememberMe = null;
        if (null !== $response) {
            $user = $this->mockUser();
            $rememberMeHandler = $this->createMock(RememberMeHandlerInterface::class);
            $rememberMeHandler->expects($this->once())
                ->method('createRememberMeCookie')
                ->with($user);
        }

        return new LoginManager($tokenStorage, $userChecker, $sessionStrategy, $requestStack, $rememberMe);
    }

    private function mockUser()
    {
        $user = $this->getMockBuilder(UserInterface::class)->getMock();
        $user
            ->expects($this->once())
            ->method('getRoles')
            ->will($this->returnValue(['ROLE_USER']));

        return $user;
    }
}
