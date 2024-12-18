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
