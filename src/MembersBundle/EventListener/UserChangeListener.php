<?php

namespace MembersBundle\EventListener;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Mailer\Mailer;
use MembersBundle\Manager\UserManagerInterface;
use Pimcore\Event\DataObjectEvents;
use Pimcore\Event\Model\DataObjectEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserChangeListener implements EventSubscriberInterface
{
    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @var string
     */
    protected $postEventType;

    /**
     * UserChangeListener constructor.
     *
     * @param UserManagerInterface $userManager
     * @param Mailer               $pimcoreMailer
     * @param string               $postEventType
     */
    public function __construct(
        UserManagerInterface $userManager,
        Mailer $pimcoreMailer,
        string $postEventType
    ) {
        $this->userManager = $userManager;
        $this->mailer = $pimcoreMailer;
        $this->postEventType = $postEventType;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            DataObjectEvents::PRE_UPDATE => ['handleObjectUpdate', 0]
        ];
    }

    /**
     * @param DataObjectEvent $e
     */
    public function handleObjectUpdate(DataObjectEvent $e)
    {
        $user = $e->getObject();

        if (!$user instanceof UserInterface || $this->postEventType !== 'confirm_by_admin') {
            return;
        }

        if ($user->getPublished() === false) {
            return;
        }

        if ($user->getConfirmationToken() === null) {
            return;
        }

        if ($user->getPasswordRequestedAt() !== null) {
            return;
        }

        $user->setConfirmationToken(null);
        $this->userManager->updateUser($user);
        $this->mailer->sendConfirmedEmailMessage($user);
    }
}