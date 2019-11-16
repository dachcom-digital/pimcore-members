<?php

namespace MembersBundle\Mailer;

use MembersBundle\Adapter\User\UserInterface;

interface MailerInterface
{
    /**
     * Send an email to a user to confirm the account creation.
     *
     * @param UserInterface $user
     */
    public function sendConfirmationEmailMessage(UserInterface $user);

    /**
     * Send an email to a user to confirm the password reset.
     *
     * @param UserInterface $user
     */
    public function sendResettingEmailMessage(UserInterface $user);

    /**
     * Send an email to an admin when a new user has registered and awaits confirmation by an admin.
     *
     * @param UserInterface $user
     */
    public function sendAdminNotificationEmailMessage(UserInterface $user);

    /**
     * Send an email to user when and admin confirmed the user´s account.
     *
     * @param UserInterface $user
     */
    public function sendConfirmedEmailMessage(UserInterface $user);
}
