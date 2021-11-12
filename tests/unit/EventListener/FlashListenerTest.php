<?php

namespace DachcomBundle\Test\unit\EventListener;

use MembersBundle\EventListener\FlashListener;
use MembersBundle\MembersEvents;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;

class FlashListenerTest extends TestCase
{
    private Event $event;
    private FlashListener $listener;

    public function setUp(): void
    {
        $this->event = new Event();
        $flashBag = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\Flash\FlashBag')->getMock();
        $session = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\Session')->disableOriginalConstructor()->getMock();
        $session
            ->expects($this->once())
            ->method('getFlashBag')
            ->willReturn($flashBag);

        $translator = $this->getMockBuilder('Pimcore\Translation\Translator')->getMock();
        $this->listener = new FlashListener($session, $translator);
    }

    public function testAddSuccessFlash(): void
    {
        $this->listener->addSuccessFlash($this->event, MembersEvents::CHANGE_PASSWORD_COMPLETED);
    }
}