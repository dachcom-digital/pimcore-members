<?php

namespace MembersBundle\EventListener;

use MembersBundle\Event\StaticRouteEvent;
use MembersBundle\Manager\RestrictionManager;
use MembersBundle\Manager\RestrictionManagerInterface;
use MembersBundle\MembersEvents;
use MembersBundle\Restriction\ElementRestriction;
use Pimcore\Bundle\CoreBundle\EventListener\Traits\PimcoreContextAwareTrait;
use Pimcore\Http\Request\Resolver\PimcoreContextResolver;
use Pimcore\Http\RequestHelper;
use Pimcore\Model\DataObject\AbstractObject;
use Symfony\Cmf\Bundle\RoutingBundle\Routing\DynamicRouter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ForbiddenRouteListener implements EventSubscriberInterface
{
    use PimcoreContextAwareTrait;

    protected RestrictionManagerInterface $restrictionManager;
    protected RouterInterface $router;
    protected RequestHelper $requestHelper;
    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(
        RestrictionManagerInterface $restrictionManager,
        RouterInterface $router,
        RequestHelper $requestHelper,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->restrictionManager = $restrictionManager;
        $this->router = $router;
        $this->requestHelper = $requestHelper;
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest'] //before head meta listener
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (!$this->requestHelper->isFrontendRequest($event->getRequest())) {
            return;
        }

        if (!$this->matchesPimcoreContext($event->getRequest(), PimcoreContextResolver::CONTEXT_DEFAULT)) {
            return;
        }

        $restriction = null;

        // TODO: Use `str_starts_with` function once PHP requirement is >= 8.0
        if (substr($event->getRequest()->attributes->get('_route'), 0, 9) === 'document_') {
            $document = $event->getRequest()->get(DynamicRouter::CONTENT_KEY, null);
            $restriction = $this->restrictionManager->getElementRestrictionStatus($document);
        } elseif ($event->getRequest()->attributes->get('pimcore_request_source') === 'staticroute') {
            $routeEvent = new StaticRouteEvent($event->getRequest(), $event->getRequest()->attributes->get('_route'));
            $this->eventDispatcher->dispatch(
                $routeEvent,
                MembersEvents::RESTRICTION_CHECK_STATICROUTE
            );

            $restrictionObject = $routeEvent->getStaticRouteObject();
            if ($restrictionObject instanceof AbstractObject) {
                $restriction = $this->restrictionManager->getElementRestrictionStatus($restrictionObject);
            }
        }

        if ($restriction !== false) {
            $event->getRequest()->attributes->set(RestrictionManager::REQUEST_RESTRICTION_STORAGE, $restriction);
            $restrictionRoute = $this->getRouteForRestriction($restriction);
            if ($restrictionRoute !== null) {
                $parameters = $restrictionRoute === 'members_user_security_login' ? ['_target_path' => $event->getRequest()->getUri()] : [];
                $response = new RedirectResponse($this->router->generate($restrictionRoute, $parameters));
                $event->setResponse($response);
            }
        }
    }

    private function getRouteForRestriction(ElementRestriction $elementRestriction): ?string
    {
        if ($elementRestriction->getSection() === RestrictionManager::RESTRICTION_SECTION_ALLOWED) {
            //section allowed
            return false;
        }

        if ($elementRestriction->getState() === RestrictionManager::RESTRICTION_STATE_NOT_LOGGED_IN
            && $elementRestriction->getSection() === RestrictionManager::RESTRICTION_SECTION_NOT_ALLOWED) {
                //not allowed
                return 'members_user_security_login';
            }

        if ($elementRestriction->getState() === RestrictionManager::RESTRICTION_STATE_LOGGED_IN
            && $elementRestriction->getSection() === RestrictionManager::RESTRICTION_SECTION_NOT_ALLOWED) {
            //logged in but no allowed.
            return 'members_user_restriction_refused';
        }

        return null;
    }
}
