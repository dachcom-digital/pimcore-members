<?php

namespace MembersBundle\EventListener;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Event\FormEvent;
use MembersBundle\Mailer\MailerInterface;
use MembersBundle\Manager\UserManagerInterface;
use MembersBundle\MembersEvents;
use MembersBundle\Tool\TokenGenerator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PostConfirmationListener implements EventSubscriberInterface
{
    public function __construct(
        protected RequestStack $requestStack,
        protected UserManagerInterface $userManager,
        protected MailerInterface $mailer,
        protected UrlGeneratorInterface $router,
        protected TokenGenerator $tokenGenerator,
        protected string $postEventType,
        protected string $postEventOauthType
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MembersEvents::REGISTRATION_SUCCESS => 'onRegistrationSuccess',
        ];
    }

    /**
     * @see confirmByAdmin
     * @see confirmInstant
     * @see confirmByMail
     */
    public function onRegistrationSuccess(FormEvent $event): void
    {
        $request = $event->getRequest();

        if ($request->attributes->get('_members_sso_aware', null) === true) {
            $type = $this->postEventOauthType;
        } else {
            $type = $this->postEventType;
        }

        $methodName = str_replace('_', '', lcfirst(ucwords($type, '_')));

        call_user_func_array([$this, $methodName], [$event]);
    }

    private function confirmByMail(FormEvent $event): void
    {
        /** @var UserInterface $user */
        $user = $event->getForm()->getData();

        $user->setPublished(false);
        if (null === $user->getConfirmationToken()) {
            $user->setConfirmationToken($this->tokenGenerator->generateToken());
        }

        $this->userManager->updateUser($user);
        $this->mailer->sendConfirmationEmailMessage($user);

        /** @var AttributeBagInterface $sessionBag */
        $sessionBag = $this->getSession()->getBag('members_session');
        $sessionBag->set('members_user_send_confirmation_email/email', $user->getEmail());

        $url = $this->router->generate('members_user_registration_check_email');
        $event->setResponse(new RedirectResponse($url));
    }

    private function confirmByAdmin(FormEvent $event): void
    {
        /** @var UserInterface $user */
        $user = $event->getForm()->getData();

        $user->setPublished(false);
        if (null === $user->getConfirmationToken()) {
            $user->setConfirmationToken($this->tokenGenerator->generateToken());
        }

        $this->userManager->updateUser($user);
        $this->mailer->sendAdminNotificationEmailMessage($user);

        /** @var AttributeBagInterface $sessionBag */
        $sessionBag = $this->getSession()->getBag('members_session');
        $sessionBag->set('members_user_send_confirmation_email/email', $user->getEmail());

        $url = $this->router->generate('members_user_registration_check_admin');
        $event->setResponse(new RedirectResponse($url));
    }

    private function confirmInstant(FormEvent $event): void
    {
        /** @var UserInterface $user */
        $user = $event->getForm()->getData();
        $user->setPublished(true);
        $this->userManager->updateUser($user);
    }

    private function getSession(): SessionInterface
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request === null) {
            throw new \LogicException('Cannot get the session without an active request.');
        }

        return $request->getSession();
    }
}
