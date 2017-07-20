<?php

namespace MembersBundle\EventListener;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Mailer\Mailer;
use Pimcore\Event\Model\ObjectEvent;
use Pimcore\Event\ObjectEvents;
use Pimcore\Version;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserChangeListener implements EventSubscriberInterface
{
    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * RestrictionServiceListener constructor.
     *
     * @param Mailer $pimcoreMailer
     */
    public function __construct(Mailer $pimcoreMailer)
    {
        $this->mailer = $pimcoreMailer;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ObjectEvents::POST_UPDATE => 'handleObjectUpdate'
        ];
    }

    /**
     * @param ObjectEvent $e
     */
    public function handleObjectUpdate(ObjectEvent $e)
    {
        $user = $e->getObject();

        if (!$user instanceof UserInterface) {
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