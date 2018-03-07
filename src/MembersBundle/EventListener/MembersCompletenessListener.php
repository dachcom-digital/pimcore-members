<?php

namespace MembersBundle\EventListener;

use MembersBundle\Adapter\Group\GroupInterface;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Configuration\Configuration;
use MembersBundle\Manager\ClassManagerInterface;
use Pimcore\Event\DataObjectEvents;
use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Model\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Pimcore\Bundle\AdminBundle\Security\User\TokenStorageUserResolver;

class MembersCompletenessListener implements EventSubscriberInterface
{
    /**
     * @var ClassManagerInterface
     */
    protected $classManager;

    /**
     * @var int
     */
    protected $memberStorageId;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var TokenStorageUserResolver
     */
    private $userResolver;

    /**
     * @param ClassManagerInterface    $classManager
     * @param Configuration            $configuration
     * @param TokenStorageUserResolver $tokenStorageUserResolver
     */
    public function __construct(
        ClassManagerInterface $classManager,
        Configuration $configuration,
        TokenStorageUserResolver $tokenStorageUserResolver
    ) {
        $this->classManager = $classManager;
        $this->configuration = $configuration;
        $this->userResolver   = $tokenStorageUserResolver;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            DataObjectEvents::PRE_UPDATE => [
                ['checkUniqueness', 20],
                ['checkProperties', 10]
            ]
        ];
    }

    /**
     * @param DataObjectEvent $e
     * @throws \Exception
     */
    public function checkUniqueness(DataObjectEvent $e)
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
                (int)$object->getId()
            ]);

            $elements = $memberListing->load();

            if (count($elements) > 0) {
                $foundObject = $elements[0];
                $artifact = 'email address';
                if ($foundObject->getUsername() === $object->getUsername()) {
                    $artifact = 'username';
                }
                throw new \Exception(sprintf('The %s is already used.', $artifact));
            }

        } elseif ($object instanceof GroupInterface) {
            $groupListing = $this->classManager->getGroupListing();
            $groupListing->setCondition('name = ? AND oo_id != ?', [$object->getName(), (int)$object->getId()]);
            $groupListing->setUnpublished(true);
            $elements = $groupListing->load();
            if (count($elements) > 0) {
                throw new \Exception('The group name is already used.');
            }
        }
    }

    /**
     * @param DataObjectEvent $e
     * @throws \Exception
     */
    public function checkProperties(DataObjectEvent $e)
    {
        $object = $e->getObject();

        if (!$this->userResolver->getUser() instanceof User) {
            return;
        }

        if (!$object instanceof UserInterface || $object->isPublished() === false) {
            return;
        }

        $emailTemplates = $this->configuration->getConfig('emails');

        //check for locale
        $needLocale = false;
        foreach ($emailTemplates['default'] as $template) {
            if (strpos($template, '{_locale}') !== false) {
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

            $message = "\n######################\n";
            $message .= 'This member object needs some additional properties!' . "\n";
            $message .= 'Since you have enabled localized mail templates you need to add some additional properties' . "\n";
            $message .= '(If you want to disable this message, remove the localized parameters from your members mail configuration).' . "\n";

            if ($needLocale && empty($userLocale)) {
                $message .= '- Define a "_user_locale" (text) property with a valid locale (like "de" or "en_US") before publishing the user object.' . "\n";
            }

            if ($needSite && empty($userSite)) {
                $message .= '- Define a "_site_domain" (text) property with a valid main domain (like "your-page.com") before publishing the user object.' . "\n";
            }

            $message .= "######################";

            throw new \Exception($message);
        }
    }
}