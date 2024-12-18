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

use MembersBundle\Adapter\Group\GroupInterface;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Configuration\Configuration;
use MembersBundle\Manager\ClassManagerInterface;
use Pimcore\Event\DataObjectEvents;
use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\Element\ValidationException;
use Pimcore\Model\User;
use Pimcore\Security\User\TokenStorageUserResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MembersCompletenessListener implements EventSubscriberInterface
{
    public function __construct(
        protected ClassManagerInterface $classManager,
        protected Configuration $configuration,
        protected TokenStorageUserResolver $userResolver
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DataObjectEvents::PRE_UPDATE => [
                ['checkUniqueness', 20],
                ['checkProperties', 10]
            ]
        ];
    }

    /**
     * @throws \Exception
     */
    public function checkUniqueness(DataObjectEvent $e): void
    {
        $object = $e->getObject();

        if (!$this->userResolver->getUser() instanceof User) {
            return;
        }

        if ($object instanceof UserInterface) {
            $memberListing = $this->classManager->getUserListing();
            $memberListing->setUnpublished(true);
            $memberListing->setCondition('(email = ? OR userName = ?) AND oo_id != ?', [
                $object->getEmail(),
                $object->getUsername(),
                (int) $object->getId()
            ]);

            $elements = $memberListing->getObjects();

            if (count($elements) > 0) {
                /** @var UserInterface $foundObject */
                $foundObject = $elements[0];
                $artifact = 'email address';
                if ($foundObject->getUsername() === $object->getUsername()) {
                    $artifact = 'username';
                }

                throw new ValidationException(sprintf('The %s is already used.', $artifact));
            }
        } elseif ($object instanceof GroupInterface) {
            $groupListing = $this->classManager->getGroupListing();
            $groupListing->setCondition('name = ? AND oo_id != ?', [$object->getName(), (int) $object->getId()]);
            $groupListing->setUnpublished(true);
            if (count($groupListing->getObjects()) > 0) {
                throw new ValidationException('The group name is already used.');
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function checkProperties(DataObjectEvent $e): void
    {
        $messageBlock = [];
        /** @var Concrete $object */
        $object = $e->getObject();

        if (!$this->userResolver->getUser() instanceof User) {
            return;
        }

        if (!$object instanceof UserInterface) {
            return;
        }

        if ($object->isPublished() === false) {
            return;
        }

        $emailTemplates = $this->configuration->getConfig('emails');

        //check for locale
        $needLocale = false;
        foreach ($emailTemplates['default'] as $template) {
            if (str_contains($template, '{_locale}')) {
                $needLocale = true;

                break;
            }
        }

        //check for site request
        $needSite = false;
        if (isset($emailTemplates['sites']) && count($emailTemplates['sites']) > 0) {
            $needSite = true;
        }

        $userLocale = $object->getProperty('_user_locale');
        $userSite = $object->getProperty('_site_domain');

        if (($needLocale && empty($userLocale)) || ($needSite && empty($userSite))) {
            $messageBlock[] = '<h3>This member object needs some additional properties!</h3>';
            $messageBlock[] = 'Since you have enabled localized mail templates you need to add some additional properties (If you want to disable this message, remove the localized parameters from your members mail configuration).';
            $messageBlock[] = '<ul>';

            if ($needLocale && empty($userLocale)) {
                $messageBlock[] = '<li>Define a <code>_user_locale</code> (text) property with a valid locale (like "de" or "en_US") before publishing the user object.</li>';
            }

            if ($needSite && empty($userSite)) {
                $messageBlock[] = '<li>Define a <code>_site_domain</code> (text) property with a valid main domain (like "your-page.com") before publishing the user object.</li>';
            }

            $messageBlock[] = '</ul>';

            throw new ValidationException(implode('', $messageBlock));
        }
    }
}
