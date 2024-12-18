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
use MembersBundle\Configuration\Configuration;
use Pimcore\Mail;
use Pimcore\Model\Document\Email;
use Pimcore\Model\Document\Service;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Mailer implements MailerInterface
{
    public function __construct(
        protected UrlGeneratorInterface $router,
        protected Configuration $configuration
    ) {
    }

    public function sendConfirmationEmailMessage(UserInterface $user): void
    {
        $template = $this->getMailTemplatePath('register_confirm', $user);
        $url = $this->router->generate('members_user_registration_confirm', ['token' => $user->getConfirmationToken()], UrlGeneratorInterface::ABSOLUTE_URL);

        $mailParams = [
            'user'            => $user,
            'confirmationUrl' => $url
        ];

        $this->sendMessage($template, $mailParams, (string) $user->getEmail());
    }

    public function sendConfirmedEmailMessage(UserInterface $user): void
    {
        if ($this->configuration->getConfig('send_user_mail_after_confirmed') === false) {
            return;
        }

        $template = $this->getMailTemplatePath('register_confirmed', $user);
        $url = $this->generateUrl('members_user_security_login', $user);

        $mailParams = [
            'user'      => $user,
            'loginpage' => $url
        ];

        $this->sendMessage($template, $mailParams, (string) $user->getEmail());
    }

    public function sendResettingEmailMessage(UserInterface $user): void
    {
        $template = $this->getMailTemplatePath('register_password_resetting', $user);
        $url = $this->generateUrl('members_user_resetting_reset', $user, ['token' => $user->getConfirmationToken()]);

        $mailParams = [
            'user'            => $user,
            'confirmationUrl' => $url
        ];

        $this->sendMessage($template, $mailParams, (string) $user->getEmail());
    }

    public function sendAdminNotificationEmailMessage(UserInterface $user): void
    {
        if ($this->configuration->getConfig('send_admin_mail_after_register') === false) {
            return;
        }

        $template = $this->getMailTemplatePath('admin_register_notification', $user);
        $url = $this->generateUrl('pimcore_admin_login_deeplink', $user, [], false);

        $mailParams = [
            'user'     => $user,
            'deeplink' => $url . '?' . 'object_' . $user->getId() . '_object' //thanks pimcore.
        ];

        $this->sendMessage($template, $mailParams, 'templateTo');
    }

    /**
     * @throws \Exception
     */
    protected function sendMessage(string $documentPath, array $mailParams, string $toEmail): void
    {
        $emailDocument = Email::getByPath($documentPath);
        if (!$emailDocument instanceof Email) {
            throw new \Exception(sprintf('document not found in "%s"', $documentPath));
        }

        $email = new Mail();

        if ($toEmail === 'templateTo') {
            $recipient = $emailDocument->getTo();
            if (empty($recipient)) {
                throw new \Exception(sprintf('admin email document with id "%s" does not have a valid recipient.', $emailDocument->getId()));
            }
        } else {
            $recipient = $toEmail;
        }

        $email->addTo($recipient);
        $email->setDocument($emailDocument);
        $email->setParams($mailParams);
        $email->send();
    }

    private function getMailTemplatePath(string $type, UserInterface $user): ?string
    {
        $templates = $this->configuration->getConfig('emails');

        $userLocale = $user->getProperty('_user_locale');
        $userSite = $user->getProperty('_site_domain');

        $templateBranch = $templates['default'];
        if (!empty($userSite) && !empty($templates['sites'])) {
            $key = array_search($userSite, array_column($templates['sites'], 'main_domain'), true);
            if ($key !== false) {
                $templateBranch = $templates['sites'][$key]['emails'];
            }
        }

        $requestedTemplate = $templateBranch[$type];
        if (!empty($userLocale) && str_contains($requestedTemplate, '{_locale}')) {
            $_requestedTemplate = str_replace('{_locale}', $userLocale, $requestedTemplate);

            //fallback: there is maybe a nice locale to url transform, like "de_CH" => "de-ch"
            if (str_contains($userLocale, '_') && !Service::pathExists($_requestedTemplate)) {
                $_requestedTemplate = str_replace('{_locale}', strtolower(str_replace('_', '-', $userLocale)), $requestedTemplate);
            }

            $requestedTemplate = $_requestedTemplate;
        }

        return $requestedTemplate;
    }

    private function generateUrl(string $route, UserInterface $user, array $options = [], bool $addLocale = true): string
    {
        if ($addLocale === true && !empty($user->getProperty('_user_locale'))) {
            $options['_locale'] = $user->getProperty('_user_locale');
        }

        $context = $this->router->getContext();
        if (!empty($user->getProperty('_site_domain'))) {
            $context->setHost($user->getProperty('_site_domain'));
        }

        return $this->router->generate($route, $options, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
