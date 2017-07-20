<?php

namespace MembersBundle\EventListener;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Event\FormEvent;
use MembersBundle\Event\GetResponseUserEvent;
use MembersBundle\MembersEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ResettingListener implements EventSubscriberInterface
{
    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * @var int
     */
    private $tokenTtl;

    /**
     * ResettingListener constructor.
     *
     * @param UrlGeneratorInterface $router
     * @param int                   $tokenTtl
     */
    public function __construct(UrlGeneratorInterface $router, $tokenTtl)
    {
        $this->router = $router;
        $this->tokenTtl = $tokenTtl;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            MembersEvents::RESETTING_RESET_INITIALIZE => 'onResettingResetInitialize',
            MembersEvents::RESETTING_RESET_SUCCESS    => 'onResettingResetSuccess',
            MembersEvents::RESETTING_RESET_REQUEST    => 'onResettingResetRequest',
        ];
    }

    /**
     * @param GetResponseUserEvent $event
     */
    public function onResettingResetInitialize(GetResponseUserEvent $event)
    {
        if (!$event->getUser()->isPasswordRequestNonExpired($this->tokenTtl)) {
            $event->setResponse(new RedirectResponse($this->router->generate('members_user_resetting_request')));
        }
    }

    /**
     * @param FormEvent $event
     */
    public function onResettingResetSuccess(FormEvent $event)
    {
        /** @var $user UserInterface */
        $user = $event->getForm()->getData();

        $user->setConfirmationToken(NULL);
        $user->setPasswordRequestedAt(NULL);
        $user->setPublished(TRUE);
    }

    /**
     * @param GetResponseUserEvent $event
     */
    public function onResettingResetRequest(GetResponseUserEvent $event)
    {
        if (!$event->getUser()->isAccountNonLocked()) {
            $event->setResponse(new RedirectResponse($this->router->generate('members_user_resetting_request')));
        }
    }
}
