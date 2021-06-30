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
    private UserManagerInterface $userManager;
    private UrlGeneratorInterface $router;
    private int $tokenTtl;

    public function __construct(
        UserManagerInterface $userManager,
        UrlGeneratorInterface $router,
        int $tokenTtl
    ) {
        $this->userManager = $userManager;
        $this->router = $router;
        $this->tokenTtl = $tokenTtl;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MembersEvents::RESETTING_RESET_INITIALIZE => 'onResettingResetInitialize',
            MembersEvents::RESETTING_RESET_SUCCESS    => 'onResettingResetSuccess',
            MembersEvents::RESETTING_RESET_REQUEST    => 'onResettingResetRequest',
        ];
    }

    public function onResettingResetInitialize(GetResponseUserEvent $event): void
    {
        if (!$event->getUser()->isPasswordRequestNonExpired($this->tokenTtl)) {
            $event->setResponse(new RedirectResponse($this->router->generate('members_user_resetting_request')));
        }
    }

    public function onResettingResetSuccess(FormEvent $event): void
    {
        /** @var UserInterface $user */
        $user = $event->getForm()->getData();

        $user->setConfirmationToken(null);
        $user->setPasswordRequestedAt(null);
        $user->setPublished(true);
        $this->userManager->updateUser($user);
    }

    public function onResettingResetRequest(GetResponseUserEvent $event): void
    {
        if (!$event->getUser()->isAccountNonLocked()) {
            $event->setResponse(new RedirectResponse($this->router->generate('members_user_resetting_request')));
        }
    }
}
