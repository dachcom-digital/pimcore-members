<?php

namespace DachcomBundle\Test\Support\Services;

use MembersBundle\Event\StaticRouteEvent;
use MembersBundle\MembersEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Pimcore\Model\DataObject\TestClass;

class TestRestrictedStaticRouteListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            MembersEvents::RESTRICTION_CHECK_STATICROUTE => 'checkStaticRoute'
        ];
    }

    public function checkStaticRoute(StaticRouteEvent $event): void
    {
        if (str_starts_with($event->getRouteName(), 'test_route') === false) {
            return;
        }

        $request = $event->getRequest();
        $object = TestClass::getById($request->attributes->get('object_id'));

        $event->setStaticRouteObject($object);
    }
}
