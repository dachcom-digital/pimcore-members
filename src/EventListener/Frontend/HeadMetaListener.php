<?php

namespace MembersBundle\EventListener\Frontend;

use MembersBundle\Manager\RestrictionManager;
use Pimcore\Bundle\CoreBundle\EventListener\Frontend\AbstractFrontendListener;
use Pimcore\Service\Request\DocumentResolver as DocumentResolverService;
use Pimcore\Service\Request\PimcoreContextResolver;
use Pimcore\Templating\Helper\HeadMeta;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Adds Meta Data entries of document to HeadMeta view helper
 */
class HeadMetaListener extends AbstractFrontendListener implements EventSubscriberInterface
{
    /**
     * @var DocumentResolverService
     */
    protected $documentResolverService;

    /**
     * @var HeadMeta
     */
    protected $headMeta;

    /**
     * @var RestrictionManager
     */
    protected $restrictionManager;

    /**
     * @param DocumentResolverService $documentResolverService
     * @param HeadMeta                $headMeta
     * @param RestrictionManager      $restrictionManager
     */
    public function __construct(
        RestrictionManager $restrictionManager,
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

        $restrictionStorage = $event->getRequest()->attributes->get(RestrictionManager::REQUEST_RESTRICTION_STORAGE);
        if(!is_null($restrictionStorage)) {
            $this->headMeta->appendName('m:groups', implode(',', $restrictionStorage['current_route_restriction_groups']));
        }
    }
}