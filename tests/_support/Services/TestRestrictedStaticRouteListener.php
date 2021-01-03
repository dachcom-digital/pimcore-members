<?php

namespace DachcomBundle\Test\Services;

use MembersBundle\Event\StaticRouteEvent;
use MembersBundle\MembersEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Pimcore\Model\DataObject\TestClass;

class TestRestrictedStaticRouteListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            MembersEvents::RESTRICTION_CHECK_STATICROUTE => 'checkStaticRoute'
        ];
    }

    public function checkStaticRoute(StaticRouteEvent $event)
    {
        if($event->getRouteName() !== 'test_route') {
            return;
        }

        $request = $event->getRequest();
        $object = TestClass::getById($request->attributes->get('object_id'));

        $event->setStaticRouteObject($object);
    }
}
