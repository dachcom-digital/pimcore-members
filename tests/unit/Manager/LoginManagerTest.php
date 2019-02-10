<?php

namespace DachcomBundle\Test\unit\Manager;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Manager\LoginManager;
use MembersBundle\Security\UserChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\RememberMe\RememberMeServicesInterface;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;

class LoginManagerTest extends TestCase
{
    public function testLogInUserWithRequestStack()
    {
        $loginManager = $this->createLoginManager();
        $loginManager->logInUser('main', $this->mockUser());
    }

    public function testLogInUserWithRememberMeAndRequestStack()
    {
        $response = $this->getMockBuilder(Response::class)->getMock();
        $loginManager = $this->createLoginManager($response);
        $loginManager->logInUser('main', $this->mockUser(), $response);
    }

    /**
     * @param Response|null $response
     *
     * @return LoginManager
     */
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
            ->will($this->returnValue($request));

        $rememberMe = null;
        if (null !== $response) {
            $rememberMe = $this->getMockBuilder(RememberMeServicesInterface::class)->getMock();
            $rememberMe
                ->expects($this->once())
                ->method('loginSuccess')
                ->with($request, $response, $this->isInstanceOf(TokenInterface::class));
        }

        return new LoginManager($tokenStorage, $userChecker, $sessionStrategy, $requestStack, $rememberMe);
    }

    /**
     * @return mixed
     */
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