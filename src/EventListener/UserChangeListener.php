<?php

namespace MembersBundle\EventListener;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Configuration\Configuration;
use MembersBundle\Mailer\Mailer;
use Pimcore\Event\Model\ObjectEvent;
use Pimcore\Event\ObjectEvents;
use Pimcore\Model\Version;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserChangeListener implements EventSubscriberInterface
{
    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * RestrictionServiceListener constructor.
     *
     * @param Mailer $pimcoreMailer
     * @param Configuration $configuration
     */
    public function __construct(Mailer $pimcoreMailer, Configuration $configuration)
    {
        $this->mailer = $pimcoreMailer;
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ObjectEvents::PRE_UPDATE => 'handleObjectUpdate'
        ];
    }

    /**
     * @param ObjectEvent $e
     */
    public function handleObjectUpdate(ObjectEvent $e)
    {
        $user = $e->getObject();

        if (!$user instanceof UserInterface
            || $this->configuration->getConfig('post_register_type') !== 'confirm_by_admin') {
            return;
        }

        $couldSendMail = FALSE;
        $versionIsPublished = FALSE;
        $userLastVersion = $user->getLatestVersion(TRUE);

        if ($userLastVersion instanceof Version) {
            $versionIsPublished = $userLastVersion->getData()->getPublished();
        }

        if ($versionIsPublished === FALSE && $user->getPublished() === TRUE) {
            $couldSendMail = TRUE;
        }

        if($couldSendMail) {
            $this->mailer->sendConfirmedEmailMessage($user);
        }
    }
}