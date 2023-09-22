<?php

namespace MembersBundle\EventListener;

use Pimcore\Event\AssetEvents;
use Pimcore\Event\DocumentEvents;
use Pimcore\Event\Model\AssetEvent;
use Pimcore\Event\Model\DocumentEvent;
use Pimcore\Event\DataObjectEvents;
use Pimcore\Event\Model\DataObjectEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use MembersBundle\Service\RestrictionService;

class RestrictionStoreListener implements EventSubscriberInterface
{
    protected RestrictionService $serviceRestriction;

    public function __construct(RestrictionService $serviceRestriction)
    {
        $this->serviceRestriction = $serviceRestriction;
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
