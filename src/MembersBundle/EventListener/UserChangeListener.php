<?php

namespace MembersBundle\EventListener;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Configuration\Configuration;
use MembersBundle\Mailer\Mailer;
use MembersBundle\Manager\UserManagerInterface;
use Pimcore\Event\DataObjectEvents;
use Pimcore\Event\Model\DataObjectEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserChangeListener implements EventSubscriberInterface
{
    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * UserChangeListener constructor.
     *
     * @param UserManagerInterface $userManager
     * @param Mailer               $pimcoreMailer
     * @param Configuration        $configuration
     */
    public function __construct(UserManagerInterface $userManager, Mailer $pimcoreMailer, Configuration $configuration)
    {
        $this->userManager = $userManager;
        $this->mailer = $pimcoreMailer;
        $this->configuration = $configuration;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            DataObjectEvents::PRE_UPDATE => ['handleObjectUpdate', 0]
        ];
    }

    /**
     * @param DataObjectEvent $e
     */
    public function handleObjectUpdate(DataObjectEvent $e)
    {
        $user = $e->getObject();

        if (!$user instanceof UserInterface
            || $this->configuration->getConfig('post_register_type') !== 'confirm_by_admin') {
            return;
        }

        if ($user->getPublished() === false) {
            return;
        }

        if ($user->getConfirmationToken() === null) {
            return;
        }

        $user->setConfirmationToken(null);
        $this->userManager->updateUser($user);
        $this->mailer->sendConfirmedEmailMessage($user);
    }
}