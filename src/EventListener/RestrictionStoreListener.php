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

namespace MembersBundle\EventListener;

use MembersBundle\Service\RestrictionService;
use Pimcore\Event\AssetEvents;
use Pimcore\Event\DataObjectEvents;
use Pimcore\Event\DocumentEvents;
use Pimcore\Event\Model\AssetEvent;
use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Event\Model\DocumentEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RestrictionStoreListener implements EventSubscriberInterface
{
    public function __construct(protected RestrictionService $serviceRestriction)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DataObjectEvents::PRE_DELETE => 'handleObjectDeletion',
            DocumentEvents::PRE_DELETE   => 'handleDocumentDeletion',
            AssetEvents::PRE_DELETE      => 'handleAssetDeletion',

            DataObjectEvents::POST_ADD => 'handleObjectAdd',
            DocumentEvents::POST_ADD   => 'handleDocumentAdd',
            AssetEvents::POST_ADD      => 'handleAssetAdd',

            DataObjectEvents::POST_UPDATE => 'handleObjectUpdate',
            DocumentEvents::POST_UPDATE   => 'handleDocumentUpdate',
            AssetEvents::POST_UPDATE      => 'handleAssetUpdate'
        ];
    }

    public function handleDocumentDeletion(DocumentEvent $e): void
    {
        $this->serviceRestriction->deleteRestriction($e->getDocument(), 'page');
    }

    public function handleAssetDeletion(AssetEvent $e): void
    {
        $this->serviceRestriction->deleteRestriction($e->getAsset(), 'asset');
    }

    public function handleObjectDeletion(DataObjectEvent $e): void
    {
        $this->serviceRestriction->deleteRestriction($e->getObject(), 'object');
    }

    public function handleObjectAdd(DataObjectEvent $e): void
    {
        $this->serviceRestriction->checkRestrictionContext($e->getObject(), 'object');
    }

    public function handleDocumentAdd(DocumentEvent $e): void
    {
        $this->serviceRestriction->checkRestrictionContext($e->getDocument(), 'page');
    }

    public function handleAssetAdd(AssetEvent $e): void
    {
        $this->serviceRestriction->checkRestrictionContext($e->getAsset(), 'asset');
    }

    public function handleObjectUpdate(DataObjectEvent $e): void
    {
        //only trigger update if object gets moved.
        //default restriction page update gets handled by restrictionController or API.
        if ($e->hasArgument('oldPath') === false) {
            return;
        }

        $this->serviceRestriction->checkRestrictionContext($e->getObject(), 'object');
    }

    public function handleDocumentUpdate(DocumentEvent $e): void
    {
        //only trigger update if page gets moved.
        //default restriction page update gets handled by restrictionController or API.
        if ($e->hasArgument('oldPath') === false) {
            return;
        }

        $this->serviceRestriction->checkRestrictionContext($e->getDocument(), 'page');
    }

    public function handleAssetUpdate(AssetEvent $e): void
    {
        //only trigger update if asset gets moved.
        //default restriction page update gets handled by restrictionController or API.
        if ($e->hasArgument('oldPath') === false) {
            return;
        }

        $this->serviceRestriction->checkRestrictionContext($e->getAsset(), 'asset');
    }
}
