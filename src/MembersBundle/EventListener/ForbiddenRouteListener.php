<?php

namespace MembersBundle\EventListener;

use MembersBundle\Event\StaticRouteEvent;
use MembersBundle\Manager\RestrictionManager;
use MembersBundle\MembersEvents;
use Pimcore\Bundle\CoreBundle\EventListener\Frontend\AbstractFrontendListener;
use Pimcore\Http\RequestHelper;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Service\Request\PimcoreContextResolver;
use Symfony\Cmf\Bundle\RoutingBundle\Routing\DynamicRouter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\RouterInterface;

class ForbiddenRouteListener extends AbstractFrontendListener implements EventSubscriberInterface
{
    /**
     * @var RestrictionManager
     */
    protected $restrictionManager;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var RequestHelper
     */
    private $requestHelper;

    /**
     * ForbiddenRouteListener constructor.
     *
     * @param RestrictionManager $restrictionManager
     * @param RouterInterface    $r
     * @param RequestHelper    $requestHelper
     */
    public function __construct(RestrictionManager $restrictionManager, RouterInterface $r, RequestHelper $requestHelper)
    {
        $this->restrictionManager = $restrictionManager;
        $this->router = $r;
        $this->requestHelper = $requestHelper;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest'] //before head meta listener
        ];
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if (!$this->requestHelper->isFrontendRequest($event->getRequest())) {
            return;
        }

        if (!$this->matchesPimcoreContext($event->getRequest(), PimcoreContextResolver::CONTEXT_DEFAULT)) {
            return;
        }

        $restriction = FALSE;

        if (strpos($event->getRequest()->attributes->get('_route'), 'document_') !== FALSE) {
            $document = $event->getRequest()->get(DynamicRouter::CONTENT_KEY, NULL);
            $restriction = $this->restrictionManager->getElementRestrictionStatus($document);
        } elseif ($event->getRequest()->attributes->get('pimcore_request_source') === 'staticroute') {

            $routeEvent = new StaticRouteEvent($event->getRequest(), $event->getRequest()->attributes->get('_route'));
            \Pimcore::getEventDispatcher()->dispatch(
                MembersEvents::RESTRICTION_CHECK_STATICROUTE,
                $routeEvent
            );

            $restrictionObject = $routeEvent->getStaticRouteObject();
            if($restrictionObject instanceof AbstractObject) {
                $restriction = $this->restrictionManager->getElementRestrictionStatus($restrictionObject);
            }
        }

        if ($restriction !== FALSE) {
            $event->getRequest()->attributes->set(RestrictionManager::REQUEST_RESTRICTION_STORAGE, $restriction);
            $restrictionRoute = $this->getRouteForRestriction($restriction);
            if ($restrictionRoute !== FALSE) {
                $response = new RedirectResponse($this->router->generate($restrictionRoute));
                $event->setResponse($response);
            }
        }
    }

    /**
     * @param $restriction
     *
     * @return bool|string
     */
    private function getRouteForRestriction($restriction)
    {
        //section allowed
        if ($restriction['section'] == RestrictionManager::RESTRICTION_SECTION_ALLOWED) {
            return FALSE;
        } //not allowed
        else if ($restriction['state'] === RestrictionManager::RESTRICTION_STATE_NOT_LOGGED_IN
            && $restriction['section'] === RestrictionManager::RESTRICTION_SECTION_NOT_ALLOWED
        ) {
            return 'members_user_security_login';
        } //logged in but no allowed.
        else if ($restriction['state'] === RestrictionManager::RESTRICTION_STATE_LOGGED_IN
            && $restriction['section'] === RestrictionManager::RESTRICTION_SECTION_NOT_ALLOWED
        ) {
            return 'members_user_restriction_refused';
        }

        return FALSE;
    }
}