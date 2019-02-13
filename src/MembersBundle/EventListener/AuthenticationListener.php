<?php

namespace MembersBundle\EventListener;

use MembersBundle\Event\FilterUserResponseEvent;
use MembersBundle\Event\UserEvent;
use MembersBundle\MembersEvents;
use MembersBundle\Manager\LoginManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Exception\AccountStatusException;

class AuthenticationListener implements EventSubscriberInterface
{
    /**
     * @var LoginManagerInterface
     */
    private $loginManager;

    /**
     * @var string
     */
    private $firewallName;

    /**
     * AuthenticationListener constructor.
     *
     * @param LoginManagerInterface $loginManager
     * @param string                $firewallName
     */
    public function __construct(LoginManagerInterface $loginManager, $firewallName = 'members_fe')
    {
        $this->loginManager = $loginManager;
        $this->firewallName = $firewallName;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            MembersEvents::REGISTRATION_COMPLETED    => 'authenticate',
            MembersEvents::REGISTRATION_CONFIRMED    => 'authenticate',
            MembersEvents::RESETTING_RESET_COMPLETED => 'authenticate',
        ];
    }

    /**
     * @param FilterUserResponseEvent  $event
     * @param string                   $eventName
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function authenticate(FilterUserResponseEvent $event, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        try {
            $this->loginManager->logInUser($this->firewallName, $event->getUser(), $event->getResponse());
            $eventDispatcher->dispatch(MembersEvents::SECURITY_IMPLICIT_LOGIN, new UserEvent($event->getUser(), $event->getRequest()));
        } catch (AccountStatusException $ex) {
        }
    }
}
