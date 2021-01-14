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
    /**
     * @var RestrictionService
     */
    protected $serviceRestriction;

    /**
     * @param RestrictionService $serviceRestriction
     */
    public function __construct(RestrictionService $serviceRestriction)
    {
        $this->serviceRestriction = $serviceRestriction;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
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

    /**
     * @param DocumentEvent $e
     */
    public function handleDocumentDeletion(DocumentEvent $e)
    {
        $this->serviceRestriction->deleteRestriction($e->getDocument(), 'page');
    }

    /**
     * @param AssetEvent $e
     */
    public function handleAssetDeletion(AssetEvent $e)
    {
        $this->serviceRestriction->deleteRestriction($e->getAsset(), 'asset');
    }

    /**
     * @param DataObjectEvent $e
     */
    public function handleObjectDeletion(DataObjectEvent $e)
    {
        $this->serviceRestriction->deleteRestriction($e->getObject(), 'object');
    }

    /**
     * @param DataObjectEvent $e
     */
    public function handleObjectAdd(DataObjectEvent $e)
    {
        $this->serviceRestriction->checkRestrictionContext($e->getObject(), 'object');
    }

    /**
     * @param DocumentEvent $e
     */
    public function handleDocumentAdd(DocumentEvent $e)
    {
        $this->serviceRestriction->checkRestrictionContext($e->getDocument(), 'page');
    }

    /**
     * @param AssetEvent $e
     */
    public function handleAssetAdd(AssetEvent $e)
    {
        $this->serviceRestriction->checkRestrictionContext($e->getAsset(), 'asset');
    }

    /**
     * @param DataObjectEvent $e
     */
    public function handleObjectUpdate(DataObjectEvent $e)
    {
        //only trigger update if object gets moved.
        //default restriction page update gets handled by restrictionController or API.
        if ($e->hasArgument('oldPath') === false) {
            return;
        }

        $this->serviceRestriction->checkRestrictionContext($e->getObject(), 'object');
    }

    /**
     * @param DocumentEvent $e
     */
    public function handleDocumentUpdate(DocumentEvent $e)
    {
        //only trigger update if page gets moved.
        //default restriction page update gets handled by restrictionController or API.
        if ($e->hasArgument('oldPath') === false) {
            return;
        }

        $this->serviceRestriction->checkRestrictionContext($e->getDocument(), 'page');
    }

    /**
     * @param AssetEvent $e
     */
    public function handleAssetUpdate(AssetEvent $e)
    {
        //only trigger update if asset gets moved.
        //default restriction page update gets handled by restrictionController or API.
        if ($e->hasArgument('oldPath') === false) {
            return;
        }

        $this->serviceRestriction->checkRestrictionContext($e->getAsset(), 'asset');
    }
}
