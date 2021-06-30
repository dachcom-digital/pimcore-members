<?php

namespace MembersBundle\Mailer;

use MembersBundle\Adapter\User\UserInterface;

interface MailerInterface
{
    /**
     * Send an email to a user to confirm the account creation.
     */
    public function sendConfirmationEmailMessage(UserInterface $user): void;

    /**
     * Send an email to a user to confirm the password reset.
     */
    public function sendResettingEmailMessage(UserInterface $user): void;

    /**
     * Send an email to an admin when a new user has registered and awaits confirmation by an admin.
     */
    public function sendAdminNotificationEmailMessage(UserInterface $user): void;

    /**
     * Send an email to user when and admin confirmed the user´s account.
     */
    public function sendConfirmedEmailMessage(UserInterface $user): void;
}
