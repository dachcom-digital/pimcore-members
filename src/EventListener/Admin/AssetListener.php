<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

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
