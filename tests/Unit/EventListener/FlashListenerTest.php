<?php

namespace DachcomBundle\Test\Unit\EventListener;

use MembersBundle\EventListener\FlashListener;
use MembersBundle\MembersEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Session;
use DachcomBundle\Test\Support\Test\DachcomBundleTestCase;

class FlashListenerTest extends DachcomBundleTestCase
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


        $request = new Request();
        $request->setSession($session);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        // nah.
        $translator = \Pimcore::getContainer()->get(TranslatorInterface::class);

        $this->listener = new FlashListener($requestStack, $translator);
    }

    public function testAddSuccessFlash(): void
    {
        $this->listener->addSuccessFlash($this->event, MembersEvents::CHANGE_PASSWORD_COMPLETED);
    }
}
