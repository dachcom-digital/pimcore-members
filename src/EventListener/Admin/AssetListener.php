<?php

namespace MembersBundle\EventListener\Admin;

use Pimcore\Event\BundleManager\PathsEvent;
use Pimcore\Event\BundleManagerEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AssetListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            BundleManagerEvents::CSS_PATHS          => 'addCssFiles',
            BundleManagerEvents::EDITMODE_CSS_PATHS => 'addEditModeCssFiles',
            BundleManagerEvents::JS_PATHS           => 'addJsFiles'
        ];
    }

    public function addCssFiles(PathsEvent $event): void
    {
        $event->addPaths([
            '/bundles/members/css/admin.css'
        ]);
    }

    public function addEditModeCssFiles(PathsEvent $event): void
    {
        $event->addPaths([
            '/bundles/members/css/admin-editmode.css',
        ]);
    }

    public function addJsFiles(PathsEvent $event): void
    {
        $event->addPaths([
            '/bundles/members/js/backend/startup.js',
            '/bundles/members/js/backend/document/restriction.js',
            '/bundles/members/js/pimcore/js/coreExtension/data/dataMultiselect.js',
            '/bundles/members/js/pimcore/js/coreExtension/data/membersGroupMultiselect.js',
            '/bundles/members/js/pimcore/js/coreExtension/tags/multiselect.js',
            '/bundles/members/js/pimcore/js/coreExtension/tags/membersGroupMultiselect.js'
        ]);
    }
}
