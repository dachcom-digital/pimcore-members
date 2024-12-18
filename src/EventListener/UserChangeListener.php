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
