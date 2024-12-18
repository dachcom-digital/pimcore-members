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

use MembersBundle\Event\FilterUserResponseEvent;
use MembersBundle\Event\UserEvent;
use MembersBundle\Manager\LoginManagerInterface;
use MembersBundle\MembersEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AuthenticationListener implements EventSubscriberInterface
{
    public function __construct(
        private LoginManagerInterface $loginManager,
        private string $firewallName = 'members_fe'
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MembersEvents::REGISTRATION_COMPLETED    => 'authenticate',
            MembersEvents::REGISTRATION_CONFIRMED    => 'authenticate',
            MembersEvents::RESETTING_RESET_COMPLETED => 'authenticate',
        ];
    }

    public function authenticate(FilterUserResponseEvent $event, string $eventName, EventDispatcherInterface $eventDispatcher): void
    {
        try {
            $this->loginManager->logInUser($this->firewallName, $event->getUser(), $event->getResponse());
            $eventDispatcher->dispatch(new UserEvent($event->getUser(), $event->getRequest()), MembersEvents::SECURITY_IMPLICIT_LOGIN);
        } catch (AccountStatusException $ex) {
            // fail silently
        }
    }
}
