<?php

namespace MembersBundle\EventListener;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Event\FormEvent;
use MembersBundle\Mailer\Mailer;
use MembersBundle\Manager\UserManagerInterface;
use MembersBundle\MembersEvents;
use MembersBundle\Tool\TokenGenerator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PostConfirmationListener implements EventSubscriberInterface
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
     * @var TokenGenerator
     */
    protected $tokenGenerator;

    /**
     * @var UrlGeneratorInterface
     */
    protected $router;

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var string
     */
    protected $postEventType;

    /**
     * EmailConfirmationListener constructor.
     *
     * @param UserManagerInterface  $userManager
     * @param Mailer                $pimcoreMailer
     * @param UrlGeneratorInterface $router
     * @param SessionInterface      $session
     * @param TokenGenerator        $tokenGenerator
     * @param string                $postEventType
     */
    public function __construct(
        UserManagerInterface $userManager,
        Mailer $pimcoreMailer,
        UrlGeneratorInterface $router,
        SessionInterface $session,
        TokenGenerator $tokenGenerator,
        $postEventType
    ) {
        $this->userManager = $userManager;
        $this->mailer = $pimcoreMailer;
        $this->tokenGenerator = $tokenGenerator;
        $this->router = $router;
        $this->session = $session;
        $this->postEventType = $postEventType;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            MembersEvents::REGISTRATION_SUCCESS => 'onRegistrationSuccess',
        ];
    }

    /**
     * @see confirmByMail
     * @see confirmByAdmin
     * @see confirmInstant
     *
     * @param FormEvent $event
     */
    public function onRegistrationSuccess(FormEvent $event)
    {
        $methodName = str_replace('_', '', lcfirst(ucwords($this->postEventType, '_')));
        call_user_func_array([$this, $methodName], [$event]);
    }

    /**
     * @param FormEvent $event
     */
    private function confirmByMail(FormEvent $event)
    {
        /** @var $user UserInterface */
        $user = $event->getForm()->getData();

        $user->setPublished(false);
        if (null === $user->getConfirmationToken()) {
            $user->setConfirmationToken($this->tokenGenerator->generateToken());
        }

        $this->userManager->updateUser($user);
        $this->mailer->sendConfirmationEmailMessage($user);

        /** @var \Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag $sessionBag */
        $sessionBag = $this->session->getBag('members_session');
        $sessionBag->set('members_user_send_confirmation_email/email', $user->getEmail());

        $url = $this->router->generate('members_user_registration_check_email');
        $event->setResponse(new RedirectResponse($url));
    }

    /**
     * @param FormEvent $event
     */
    private function confirmByAdmin(FormEvent $event)
    {
        /** @var $user UserInterface */
        $user = $event->getForm()->getData();

        $user->setPublished(false);
        if (null === $user->getConfirmationToken()) {
            $user->setConfirmationToken($this->tokenGenerator->generateToken());
        }

        $this->userManager->updateUser($user);
        $this->mailer->sendAdminNotificationEmailMessage($user);

        /** @var \Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag $sessionBag */
        $sessionBag = $this->session->getBag('members_session');
        $sessionBag->set('members_user_send_confirmation_email/email', $user->getEmail());

        $url = $this->router->generate('members_user_registration_check_admin');
        $event->setResponse(new RedirectResponse($url));
    }

    /**
     * @param FormEvent $event
     */
    private function confirmInstant(FormEvent $event)
    {
        /** @var $user UserInterface */
        $user = $event->getForm()->getData();
        $user->setPublished(true);
        $this->userManager->updateUser($user);
    }
}
