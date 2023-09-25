<?php

namespace DachcomBundle\Test\Unit\EventListener;

use MembersBundle\EventListener\FlashListener;
use MembersBundle\MembersEvents;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Session;

class FlashListenerTest extends TestCase
{
    private Event $event;
    private FlashListener $listener;

    public function setUp(): void
    {
        $this->event = new Event();
        $flashBag = $this->getMockBuilder(FlashBag::class)->getMock();
        $session = $this->getMockBuilder(Session::class)->disableOriginalConstructor()->getMock();
        $session
            ->expects($this->once())
            ->method('getFlashBag')
            ->willReturn($flashBag);

        // nah.
        $translator = \Pimcore::getContainer()->get(TranslatorInterface::class);

        $this->listener = new FlashListener($session, $translator);
    }

    public function testAddSuccessFlash(): void
    {
        $this->listener->addSuccessFlash($this->event, MembersEvents::CHANGE_PASSWORD_COMPLETED);
    }
}