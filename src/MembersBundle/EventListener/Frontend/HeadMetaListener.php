<?php

namespace MembersBundle\EventListener\Frontend;

use MembersBundle\Manager\RestrictionManager;
use MembersBundle\Manager\RestrictionManagerInterface;
use MembersBundle\Restriction\ElementRestriction;
use Pimcore\Bundle\CoreBundle\EventListener\Traits\PimcoreContextAwareTrait;
use Pimcore\Http\Request\Resolver\DocumentResolver as DocumentResolverService;
use Pimcore\Http\Request\Resolver\PimcoreContextResolver;
use Pimcore\Twig\Extension\Templating\HeadMeta;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Adds Meta Data entries of document to HeadMeta view helper.
 */
class HeadMetaListener implements EventSubscriberInterface
{
    use PimcoreContextAwareTrait;

    protected DocumentResolverService $documentResolverService;
    protected HeadMeta $headMeta;
    protected RestrictionManagerInterface $restrictionManager;

    public function __construct(
        RestrictionManagerInterface $restrictionManager,
        DocumentResolverService $documentResolverService,
        HeadMeta $headMeta
    ) {
        $this->documentResolverService = $documentResolverService;
        $this->headMeta = $headMeta;
        $this->restrictionManager = $restrictionManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', -20] //after forbidden route listener!
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        // just add meta data on master request
        if (!$event->isMainRequest()) {
            return;
        }

        if (!$this->matchesPimcoreContext($event->getRequest(), PimcoreContextResolver::CONTEXT_DEFAULT)) {
            return;
        }

        $groups = ['default'];

        $elementRestriction = $event->getRequest()->attributes->get(RestrictionManager::REQUEST_RESTRICTION_STORAGE);
        if ($elementRestriction instanceof ElementRestriction && !empty($elementRestriction->getRestrictionGroups())) {
            $groups = $elementRestriction->getRestrictionGroups();
        }

        $this->headMeta->appendName('m:groups', implode(',', $groups));
    }
}
