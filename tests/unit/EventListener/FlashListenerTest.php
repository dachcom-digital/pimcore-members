<?php

namespace DachcomBundle\Test\unit\EventListener;

use MembersBundle\EventListener\FlashListener;
use MembersBundle\MembersEvents;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\Event;

class FlashListenerTest extends TestCase
{
    /**
     * @var Event
     */
    private $event;

    /**
     * @var FlashListener
     */
    private $listener;

    public function setUp()
    {
        $this->event = new Event();
        $flashBag = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\Flash\FlashBag')->getMock();
        $session = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\Session')->disableOriginalConstructor()->getMock();
        $session
            ->expects($this->once())
            ->method('getFlashBag')
            ->willReturn($flashBag);

        $translator = $this->getMockBuilder('Symfony\Contracts\Translation\TranslatorInterface')->getMock();
        $this->listener = new FlashListener($session, $translator);
    }

    public function testAddSuccessFlash()
    {
        $this->listener->addSuccessFlash($this->event, MembersEvents::CHANGE_PASSWORD_COMPLETED);
    }
}
