<?php

namespace MembersBundle\EventListener;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Event\FormEvent;
use MembersBundle\Event\GetResponseUserEvent;
use MembersBundle\Manager\UserManagerInterface;
use MembersBundle\MembersEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ResettingListener implements EventSubscriberInterface
{
    /**
     * @var UserManagerInterface
     */
    private $userManager;

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
     * @param UserManagerInterface $userManager
     * @param UrlGeneratorInterface $router
     * @param int                   $tokenTtl
     */
    public function __construct(
        UserManagerInterface $userManager,
        UrlGeneratorInterface $router,
        $tokenTtl
    ) {
        $this->userManager = $userManager;
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
        $this->userManager->updateUser($user);
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
