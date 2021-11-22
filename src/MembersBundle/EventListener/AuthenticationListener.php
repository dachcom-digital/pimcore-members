<?php

namespace MembersBundle\EventListener;

use MembersBundle\Event\FilterUserResponseEvent;
use MembersBundle\Event\UserEvent;
use MembersBundle\MembersEvents;
use MembersBundle\Manager\LoginManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Exception\AccountStatusException;

class AuthenticationListener implements EventSubscriberInterface
{
    private LoginManagerInterface $loginManager;
    private string $firewallName;

    public function __construct(LoginManagerInterface $loginManager, string $firewallName = 'members_fe')
    {
        $this->loginManager = $loginManager;
        $this->firewallName = $firewallName;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            MembersEvents::REGISTRATION_COMPLETED    => 'authenticate',
            MembersEvents::REGISTRATION_CONFIRMED    => 'authenticate',
            MembersEvents::RESETTING_RESET_COMPLETED => 'authenticate',
        ];
    }

    public function authenticate(FilterUserResponseEvent $event, string $eventName, EventDispatcherInterface $eventDispatcher): void
    {
        try {
            $this->loginManager->logInUser($this->firewallName, $event->getUser(), $event->getResponse());
            $eventDispatcher->dispatch(new UserEvent($event->getUser(), $event->getRequest()), MembersEvents::SECURITY_IMPLICIT_LOGIN);
        } catch (AccountStatusException $ex) {
            // fail silently
        }
    }
}
