<?php

namespace DachcomBundle\Test\unit\EventListener;

use MembersBundle\Event\FilterUserResponseEvent;
use MembersBundle\EventListener\AuthenticationListener;
use MembersBundle\MembersEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Codeception\TestCase\Test;

class AuthenticationListenerTest extends Test
{
    const FIREWALL_NAME = 'foo';

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var FilterUserResponseEvent
     */
    private $event;

    /**
     * @var AuthenticationListener
     */
    private $listener;

    public function setUp()
    {
        $user = $this->getMockBuilder('MembersBundle\Adapter\User\UserInterface')->getMock();
        $response = $this->getMockBuilder('Symfony\Component\HttpFoundation\Response')->getMock();
        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')->getMock();

        $this->event = new FilterUserResponseEvent($user, $request, $response);
        $this->eventDispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcher')->getMock();
        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch');

        $loginManager = $this->getMockBuilder('MembersBundle\Manager\LoginManagerInterface')->getMock();
        $this->listener = new AuthenticationListener($loginManager, self::FIREWALL_NAME);
    }

    public function testAuthenticate()
    {
        $this->listener->authenticate($this->event, MembersEvents::REGISTRATION_COMPLETED, $this->eventDispatcher);
    }
}
