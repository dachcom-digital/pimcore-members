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
     * Send email to admin to confirm the registration
     *
     * @param UserInterface $user
     */
    public function sendAdminNotificationEmailMessage(UserInterface $user);

       /**
        * Notify user after an admin confirmed the registration
        *
     * @param UserInterface $user
     */
    public function sendConfirmedEmailMessage(UserInterface $user);
}
