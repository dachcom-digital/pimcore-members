<?php

namespace MembersBundle\Mailer;

use MembersBundle\Adapter\User\UserInterface;

interface MailerInterface
{
    /**
     * Send email to a user to confirm the account creation.
     *
     * @param UserInterface $user
     */
    public function sendConfirmationEmailMessage(UserInterface $user): void;

    /**
     * Send email to a user to confirm the password reset.
     *
     * @param UserInterface $user
     */
    public function sendResettingEmailMessage(UserInterface $user): void;

    /**
     * Send email to an admin when a new user has registered and awaits confirmation by an admin.
     *
     * @param UserInterface $user
     */
    public function sendAdminNotificationEmailMessage(UserInterface $user): void;

    /**
     * Send email to user when and admin confirmed the user´s account.
     *
     * @param UserInterface $user
     */
    public function sendConfirmedEmailMessage(UserInterface $user): void;
}
