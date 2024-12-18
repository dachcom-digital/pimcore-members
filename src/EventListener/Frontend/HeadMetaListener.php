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

namespace MembersBundle\EventListener\Frontend;

use MembersBundle\Manager\RestrictionManager;
use MembersBundle\Restriction\ElementRestriction;
use Pimcore\Bundle\CoreBundle\EventListener\Traits\PimcoreContextAwareTrait;
use Pimcore\Http\Request\Resolver\PimcoreContextResolver;
use Pimcore\Twig\Extension\Templating\HeadMeta;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class HeadMetaListener implements EventSubscriberInterface
{
    use PimcoreContextAwareTrait;

    public function __construct(protected HeadMeta $headMeta)
    {
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
