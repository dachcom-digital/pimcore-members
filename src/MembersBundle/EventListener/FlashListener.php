<?php

namespace MembersBundle\EventListener;

use MembersBundle\MembersEvents;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Translation\TranslatorInterface;

class FlashListener implements EventSubscriberInterface
{
    /**
     * @var string[]
     */
    private static $successMessages = [
        MembersEvents::CHANGE_PASSWORD_COMPLETED => 'members.change_password.flash.success',
        MembersEvents::PROFILE_EDIT_COMPLETED    => 'members.profile.flash.updated',
        MembersEvents::REGISTRATION_COMPLETED    => 'members.registration.flash.user_created',
        MembersEvents::RESETTING_RESET_COMPLETED => 'members.resetting.flash.success',
    ];

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * FlashListener constructor.
     *
     * @param SessionInterface    $session
     * @param TranslatorInterface $translator
     */
    public function __construct(SessionInterface $session, TranslatorInterface $translator)
    {
        $this->session = $session;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            MembersEvents::CHANGE_PASSWORD_COMPLETED => 'addSuccessFlash',
            MembersEvents::PROFILE_EDIT_COMPLETED    => 'addSuccessFlash',
            MembersEvents::REGISTRATION_COMPLETED    => 'addSuccessFlash',
            MembersEvents::RESETTING_RESET_COMPLETED => 'addSuccessFlash',
        ];
    }

    /**
     * @param Event  $event
     * @param string $eventName
     */
    public function addSuccessFlash(Event $event, $eventName)
    {
        if (!isset(self::$successMessages[$eventName])) {
            throw new \InvalidArgumentException('This event does not correspond to a known flash message.');
        }

        if (!$this->session instanceof Session) {
            throw new \InvalidArgumentException('"%s" needs to be an instance of "%s".', get_class($this->session), Session::class);
        }

        $this->session->getFlashBag()->add('success', $this->trans(self::$successMessages[$eventName]));
    }

    /**
     * @param string $message
     * @param array  $params
     *
     * @return string
     */
    private function trans($message, array $params = [])
    {
        return $this->translator->trans($message, $params);
    }
}
