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

use MembersBundle\MembersEvents;
use Pimcore\Translation\Translator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Contracts\EventDispatcher\Event;

class FlashListener implements EventSubscriberInterface
{
    private static array $successMessages = [
        MembersEvents::CHANGE_PASSWORD_COMPLETED        => 'members.change_password.flash.success',
        MembersEvents::PROFILE_EDIT_COMPLETED           => 'members.profile.flash.updated',
        MembersEvents::REGISTRATION_COMPLETED           => 'members.registration.flash.user_created',
        MembersEvents::RESETTING_RESET_COMPLETED        => 'members.resetting.flash.success',
        MembersEvents::OAUTH_PROFILE_CONNECTION_SUCCESS => 'members.oauth.connection.success',
    ];

    public function __construct(
        protected RequestStack $requestStack,
        protected Translator $translator
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MembersEvents::CHANGE_PASSWORD_COMPLETED        => 'addSuccessFlash',
            MembersEvents::PROFILE_EDIT_COMPLETED           => 'addSuccessFlash',
            MembersEvents::REGISTRATION_COMPLETED           => 'addSuccessFlash',
            MembersEvents::RESETTING_RESET_COMPLETED        => 'addSuccessFlash',
            // oauth events
            MembersEvents::OAUTH_PROFILE_CONNECTION_SUCCESS => 'addSuccessFlash',
        ];
    }

    public function addSuccessFlash(Event $event, string $eventName): void
    {
        if (!isset(self::$successMessages[$eventName])) {
            throw new \InvalidArgumentException('This event does not correspond to a known flash message.');
        }

        $this->getFlashBag()?->add('success', $this->trans(self::$successMessages[$eventName]));
    }

    private function trans(string $message, array $params = []): string
    {
        return $this->translator->trans($message, $params);
    }

    private function getFlashBag(): ?FlashBagInterface
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request === null) {
            throw new \LogicException('Cannot get the session without an active request.');
        }

        $session = $request->getSession();
        if (!$session instanceof FlashBagAwareSessionInterface) {
            return null;
        }

        return $session->getFlashBag();
    }
}
