<?php

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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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

        $this->checkAsset($event, $event->getSubject(), $event->getArgument('frontendPath'));
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

        $this->checkAsset($event, $thumbnail->getAsset(), $event->getArgument('frontendPath'));
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

        $this->checkAsset($event, $thumbnail->getAsset(), $event->getArgument('frontendPath'));
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

        $this->checkAsset($event, $thumbnail->getAsset(), $event->getArgument('frontendPath'));
    }

    private function checkAsset(GenericEvent $event, Asset $asset, ?string $frontendPath = null): void
    {
        try {
            $assetStream = $this->restrictionUri->generateAssetStreamUrl($asset, $frontendPath);
        } catch (AccessDeniedException $e) {
            $event->setArgument('frontendPath', '/bundles/pimcoreadmin/img/filetype-not-supported.svg');

            return;
        }

        if ($assetStream === null) {
            return;
        }

        $event->setArgument('frontendPath', $assetStream);
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
