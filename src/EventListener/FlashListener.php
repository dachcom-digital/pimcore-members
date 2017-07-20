<?php

namespace MembersBundle\EventListener;

use MembersBundle\MembersEvents;
use Pimcore\Translation\Translator;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
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
     * @var Session
     */
    private $session;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * FlashListener constructor.
     *
     * @param Session    $session
     * @param Translator $translator
     */
    public function __construct(Session $session, Translator $translator)
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
            throw new \InvalidArgumentException('This event does not correspond to a known flash message');
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
