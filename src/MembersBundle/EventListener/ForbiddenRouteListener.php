<?php

namespace MembersBundle\EventListener;

use MembersBundle\Event\StaticRouteEvent;
use MembersBundle\Manager\RestrictionManager;
use MembersBundle\Manager\RestrictionManagerInterface;
use MembersBundle\MembersEvents;
use MembersBundle\Restriction\ElementRestriction;
use Pimcore\Bundle\CoreBundle\EventListener\Traits\PimcoreContextAwareTrait;
use Pimcore\Http\RequestHelper;
use Pimcore\Model\DataObject;
use Pimcore\Http\Request\Resolver\PimcoreContextResolver;
use Symfony\Cmf\Bundle\RoutingBundle\Routing\DynamicRouter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

class ForbiddenRouteListener implements EventSubscriberInterface
{
    use PimcoreContextAwareTrait;

    protected RestrictionManagerInterface $restrictionManager;
    protected RouterInterface $router;
    protected RequestHelper $requestHelper;

    public function __construct(RestrictionManagerInterface $restrictionManager, RouterInterface $router, RequestHelper $requestHelper)
    {
        $this->restrictionManager = $restrictionManager;
        $this->router = $router;
        $this->requestHelper = $requestHelper;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest'] //before head meta listener
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $restriction = null;

        if (!$event->isMainRequest()) {
            return;
        }

        if (!$this->requestHelper->isFrontendRequest($event->getRequest())) {
            return;
        }

        if (!$this->matchesPimcoreContext($event->getRequest(), PimcoreContextResolver::CONTEXT_DEFAULT)) {
            return;
        }

        if (str_starts_with($event->getRequest()->attributes->get('_route'), 'document_')) {
            $document = $event->getRequest()->get(DynamicRouter::CONTENT_KEY, null);
            $restriction = $this->restrictionManager->getElementRestrictionStatus($document);
        } elseif ($event->getRequest()->attributes->get('pimcore_request_source') === 'staticroute') {
            $routeEvent = new StaticRouteEvent($event->getRequest(), $event->getRequest()->attributes->get('_route'));
            \Pimcore::getEventDispatcher()->dispatch($routeEvent, MembersEvents::RESTRICTION_CHECK_STATICROUTE);

            $restrictionObject = $routeEvent->getStaticRouteObject();
            if ($restrictionObject instanceof DataObject) {
                $restriction = $this->restrictionManager->getElementRestrictionStatus($restrictionObject);
            }
        }

        if ($restriction === null) {
            return;
        }

        $event->getRequest()->attributes->set(RestrictionManager::REQUEST_RESTRICTION_STORAGE, $restriction);
        $restrictionRoute = $this->getRouteForRestriction($restriction);

        if ($restrictionRoute !== false) {
            $parameters = $restrictionRoute === 'members_user_security_login' ? ['_target_path' => $event->getRequest()->getPathInfo()] : [];
            $response = new RedirectResponse($this->router->generate($restrictionRoute, $parameters));
            $event->setResponse($response);
        }
    }

    private function getRouteForRestriction(ElementRestriction $elementRestriction): bool|string
    {
        if ($elementRestriction->getSection() === RestrictionManager::RESTRICTION_SECTION_ALLOWED) {
            //section allowed
            return false;
        }

        if ($elementRestriction->getState() === RestrictionManager::RESTRICTION_STATE_NOT_LOGGED_IN && $elementRestriction->getSection() === RestrictionManager::RESTRICTION_SECTION_NOT_ALLOWED) {
            //not allowed
            return 'members_user_security_login';
        }

        if ($elementRestriction->getState() === RestrictionManager::RESTRICTION_STATE_LOGGED_IN && $elementRestriction->getSection() === RestrictionManager::RESTRICTION_SECTION_NOT_ALLOWED) {
            //logged in but not allowed.
            return 'members_user_restriction_refused';
        }

        return false;
    }
}
