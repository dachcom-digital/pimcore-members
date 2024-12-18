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

use MembersBundle\Configuration\Configuration;
use MembersBundle\Security\RestrictionUri;
use Pimcore\Event\FrontendEvents;
use Pimcore\Http\Request\Resolver\PimcoreContextResolver;
use Pimcore\Model\Asset;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class AssetFrontendPathListener implements EventSubscriberInterface
{
    public function __construct(
        protected Configuration $configuration,
        protected RequestStack $requestStack,
        protected PimcoreContextResolver $contextResolver,
        protected RestrictionUri $restrictionUri
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FrontendEvents::ASSET_PATH                     => 'checkAssetPath',
            FrontendEvents::ASSET_VIDEO_THUMBNAIL          => 'checkVideoThumbnailPath',
            FrontendEvents::ASSET_VIDEO_IMAGE_THUMBNAIL    => 'checkVideoImageThumbnailPath',
            FrontendEvents::ASSET_DOCUMENT_IMAGE_THUMBNAIL => 'checkDocumentImageThumbnailPath',
            FrontendEvents::ASSET_IMAGE_THUMBNAIL          => 'checkImageThumbnailPath',
        ];
    }

    public function checkAssetPath(GenericEvent $event): void
    {
        if ($this->contextMatches() === false) {
            return;
        }

        if (!$event->getSubject() instanceof Asset) {
            return;
        }

        $this->checkAsset($event, $event->getSubject());
    }

    public function checkVideoThumbnailPath(GenericEvent $event): void
    {
        if ($this->contextMatches() === false) {
            return;
        }

        if (!$event->getSubject() instanceof Asset) {
            return;
        }

        $this->checkAsset($event, $event->getSubject());
    }

    public function checkVideoImageThumbnailPath(GenericEvent $event): void
    {
        if ($this->contextMatches() === false) {
            return;
        }

        $thumbnail = $event->getSubject();
        if (!$thumbnail instanceof Asset\Video\ImageThumbnail) {
            return;
        }

        $this->checkAsset($event, $thumbnail->getAsset());
    }

    public function checkDocumentImageThumbnailPath(GenericEvent $event): void
    {
        if ($this->contextMatches() === false) {
            return;
        }

        $thumbnail = $event->getSubject();
        if (!$thumbnail instanceof Asset\Document\ImageThumbnail) {
            return;
        }

        $this->checkAsset($event, $thumbnail->getAsset());
    }

    public function checkImageThumbnailPath(GenericEvent $event): void
    {
        if ($this->contextMatches() === false) {
            return;
        }

        $thumbnail = $event->getSubject();
        if (!$thumbnail instanceof Asset\Image\Thumbnail) {
            return;
        }

        $this->checkAsset($event, $thumbnail->getAsset());
    }

    private function checkAsset(GenericEvent $event, Asset $asset): void
    {
        if (!$event->hasArgument('frontendPath')) {
            return;
        }

        $publicAssetPath = $this->restrictionUri->generatePublicAssetUrl($asset, $event->getArgument('frontendPath'));

        if ($publicAssetPath === null) {
            return;
        }

        $event->setArgument('frontendPath', $publicAssetPath);
    }

    private function contextMatches(): bool
    {
        $restrictionConfig = $this->configuration->getConfig('restriction');

        if ($restrictionConfig['enabled'] === false) {
            return false;
        }

        if ($restrictionConfig['enable_public_asset_path_protection'] === false) {
            return false;
        }

        if (!$this->requestStack->getMainRequest() instanceof Request) {
            return false;
        }

        if (!$this->contextResolver->matchesPimcoreContext($this->requestStack->getMainRequest(), PimcoreContextResolver::CONTEXT_DEFAULT)) {
            return false;
        }

        return true;
    }
}
