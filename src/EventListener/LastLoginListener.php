<?php

namespace MembersBundle\EventListener;

use Carbon\Carbon;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Event\UserEvent;
use MembersBundle\Manager\UserManagerInterface;
use MembersBundle\MembersEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class LastLoginListener implements EventSubscriberInterface
{
    public function __construct(protected UserManagerInterface $userManager)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MembersEvents::SECURITY_IMPLICIT_LOGIN => 'onImplicitLogin',
            SecurityEvents::INTERACTIVE_LOGIN      => 'onSecurityInteractiveLogin',
        ];
    }

    public function onImplicitLogin(UserEvent $event): void
    {
        $user = $event->getUser();
        $user->setLastLogin(new Carbon());
        $this->userManager->updateUser($user);
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();

        if ($user instanceof UserInterface) {
            $user->setLastLogin(new Carbon());
            $this->userManager->updateUser($user);
        }
    }
}
