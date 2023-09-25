<?php

namespace MembersBundle\EventListener;

use MembersBundle\MembersEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Pimcore\Translation\Translator;

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

        $this->getSession()->getFlashBag()->add('success', $this->trans(self::$successMessages[$eventName]));
    }

    private function trans(string $message, array $params = []): string
    {
        return $this->translator->trans($message, $params);
    }

    private function getSession(): Session
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request === null) {
            throw new \LogicException('Cannot get the session without an active request.');
        }

        return $request->getSession();
    }
}
