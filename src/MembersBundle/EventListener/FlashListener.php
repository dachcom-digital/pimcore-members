<?php

namespace MembersBundle\EventListener;

use MembersBundle\MembersEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\Translation\TranslatorInterface;

class FlashListener implements EventSubscriberInterface
{
    private static array $successMessages = [
        MembersEvents::CHANGE_PASSWORD_COMPLETED        => 'members.change_password.flash.success',
        MembersEvents::PROFILE_EDIT_COMPLETED           => 'members.profile.flash.updated',
        MembersEvents::REGISTRATION_COMPLETED           => 'members.registration.flash.user_created',
        MembersEvents::RESETTING_RESET_COMPLETED        => 'members.resetting.flash.success',
        MembersEvents::OAUTH_PROFILE_CONNECTION_SUCCESS => 'members.oauth.connection.success',
    ];

    protected SessionInterface $session;
    protected TranslatorInterface $translator;

    public function __construct(SessionInterface $session, TranslatorInterface $translator)
    {
        $this->session = $session;
        $this->translator = $translator;
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

        if (!$this->session instanceof Session) {
            throw new \InvalidArgumentException('"%s" needs to be an instance of "%s".', get_class($this->session), Session::class);
        }

        $this->session->getFlashBag()->add('success', $this->trans(self::$successMessages[$eventName]));
    }

    private function trans(string $message): string
    {
        return $this->translator->trans($message);
    }
}
