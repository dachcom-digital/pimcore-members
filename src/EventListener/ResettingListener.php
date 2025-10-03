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

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Event\FormEvent;
use MembersBundle\Event\GetResponseUserEvent;
use MembersBundle\Manager\UserManagerInterface;
use MembersBundle\MembersEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ResettingListener implements EventSubscriberInterface
{
    public function __construct(
        private UserManagerInterface $userManager,
        private UrlGeneratorInterface $router,
        private int $tokenTtl
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MembersEvents::RESETTING_RESET_INITIALIZE => 'onResettingResetInitialize',
            MembersEvents::RESETTING_RESET_SUCCESS    => 'onResettingResetSuccess',
            MembersEvents::RESETTING_RESET_REQUEST    => 'onResettingResetRequest',
        ];
    }

    public function onResettingResetInitialize(GetResponseUserEvent $event): void
    {
        if (!$event->getUser()->isPasswordRequestNonExpired($this->tokenTtl)) {
            $event->setResponse(new RedirectResponse($this->router->generate('members_user_resetting_request')));
        }
    }

    public function onResettingResetSuccess(FormEvent $event): void
    {
        /** @var UserInterface $user */
        $user = $event->getForm()->getData();

        $user->setConfirmationToken(null);
        $user->setPasswordRequestedAt(null);
        $user->setPublished(true);
        $this->userManager->updateUser($user);
    }

    public function onResettingResetRequest(GetResponseUserEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof UserInterface) {
            return;
        }

        if (!$user->isAccountNonLocked()) {
            $event->setResponse(new RedirectResponse($this->router->generate('members_user_resetting_request')));

            return;
        }

        if ($user->getConfirmationToken() !== null) {
            $event->setResponse(new RedirectResponse($this->router->generate('members_user_resetting_request')));
        }
    }
}
