<?php

namespace DachcomBundle\Test\Unit\EventListener;

use MembersBundle\Event\FilterUserResponseEvent;
use MembersBundle\EventListener\AuthenticationListener;
use MembersBundle\MembersEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use DachcomBundle\Test\Support\Test\DachcomBundleTestCase;
use MembersBundle\Adapter\User\UserInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventDispatcher;
use MembersBundle\Manager\LoginManagerInterface;

class AuthenticationListenerTest extends DachcomBundleTestCase
{
    public const FIREWALL_NAME = 'foo';

    private EventDispatcherInterface $eventDispatcher;
    private FilterUserResponseEvent $event;
    private AuthenticationListener $listener;

    public function setUp(): void
    {
        $user = $this->getMockBuilder(UserInterface::class)->getMock();
        $response = $this->getMockBuilder(Response::class)->getMock();
        $request = $this->getMockBuilder(Request::class)->getMock();

        $this->event = new FilterUserResponseEvent($user, $request, $response);
        $this->eventDispatcher = $this->getMockBuilder(EventDispatcher::class)->getMock();
        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch');

        $loginManager = $this->getMockBuilder(LoginManagerInterface::class)->getMock();
        $this->listener = new AuthenticationListener($loginManager, self::FIREWALL_NAME);
    }

    public function testAuthenticate(): void
    {
        $this->listener->authenticate($this->event, MembersEvents::REGISTRATION_COMPLETED, $this->eventDispatcher);
    }
}
