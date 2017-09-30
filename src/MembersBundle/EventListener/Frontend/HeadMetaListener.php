<?php

namespace MembersBundle\EventListener\Frontend;

use MembersBundle\Manager\RestrictionManager;
use MembersBundle\Manager\RestrictionManagerInterface;
use MembersBundle\Restriction\ElementRestriction;
use Pimcore\Bundle\CoreBundle\EventListener\Traits\PimcoreContextAwareTrait;
use Pimcore\Http\Request\Resolver\DocumentResolver as DocumentResolverService;
use Pimcore\Http\Request\Resolver\PimcoreContextResolver;
use Pimcore\Templating\Helper\HeadMeta;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Adds Meta Data entries of document to HeadMeta view helper
 */
class HeadMetaListener implements EventSubscriberInterface
{
    use PimcoreContextAwareTrait;

    /**
     * @var DocumentResolverService
     */
    protected $documentResolverService;

    /**
     * @var HeadMeta
     */
    protected $headMeta;

    /**
     * @var RestrictionManagerInterface
     */
    protected $restrictionManager;

    /**
     * @param DocumentResolverService     $documentResolverService
     * @param HeadMeta                    $headMeta
     * @param RestrictionManagerInterface $restrictionManager
     */
    public function __construct(
        RestrictionManagerInterface $restrictionManager,
        DocumentResolverService $documentResolverService,
        HeadMeta $headMeta
    ) {
        $this->documentResolverService = $documentResolverService;
        $this->headMeta = $headMeta;
        $this->restrictionManager = $restrictionManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', -20] //after forbidden route listener!
        ];
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        // just add meta data on master request
        if (!$event->isMasterRequest()) {
            return;
        }

        if (!$this->matchesPimcoreContext($event->getRequest(), PimcoreContextResolver::CONTEXT_DEFAULT)) {
            return;
        }

        $groups = ['default'];
        /** @var ElementRestriction $restrictionElement */
        $elementRestriction = $event->getRequest()->attributes->get(RestrictionManager::REQUEST_RESTRICTION_STORAGE);
        if ($elementRestriction instanceof ElementRestriction && !empty($elementRestriction->getRestrictionGroups())) {
            $groups = $elementRestriction->getRestrictionGroups();
        }

        $this->headMeta->appendName('m:groups', implode(',', $groups));
    }
}