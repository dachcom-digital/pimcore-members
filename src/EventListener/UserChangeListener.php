<?php

namespace MembersBundle\EventListener;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Mailer\MailerInterface;
use MembersBundle\Manager\UserManagerInterface;
use Pimcore\Event\DataObjectEvents;
use Pimcore\Event\Model\DataObjectEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserChangeListener implements EventSubscriberInterface
{
    public function __construct(
        protected UserManagerInterface $userManager,
        protected MailerInterface $mailer,
        protected string $postEventType
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DataObjectEvents::PRE_UPDATE => ['handleObjectUpdate', 0]
        ];
    }

    public function handleObjectUpdate(DataObjectEvent $e): void
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
