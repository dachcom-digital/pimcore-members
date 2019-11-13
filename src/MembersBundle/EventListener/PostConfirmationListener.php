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
use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;
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
     * @var string
     */
    protected $postEventOauthType;

    /**
     * @param UserManagerInterface  $userManager
     * @param Mailer                $pimcoreMailer
     * @param UrlGeneratorInterface $router
     * @param SessionInterface      $session
     * @param TokenGenerator        $tokenGenerator
     * @param string                $postEventType
     * @param string                $postEventOauthType
     */
    public function __construct(
        UserManagerInterface $userManager,
        Mailer $pimcoreMailer,
        UrlGeneratorInterface $router,
        SessionInterface $session,
        TokenGenerator $tokenGenerator,
        string $postEventType,
        string $postEventOauthType
    ) {
        $this->userManager = $userManager;
        $this->mailer = $pimcoreMailer;
        $this->tokenGenerator = $tokenGenerator;
        $this->router = $router;
        $this->session = $session;
        $this->postEventType = $postEventType;
        $this->postEventOauthType = $postEventOauthType;
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
     * @param FormEvent $event
     *
     * @see confirmByAdmin
     * @see confirmInstant
     * @see confirmByMail
     */
    public function onRegistrationSuccess(FormEvent $event)
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

    /**
     * @param FormEvent $event
     */
    private function confirmByMail(FormEvent $event)
    {
        /** @var UserInterface $user */
        $user = $event->getForm()->getData();

        $user->setPublished(false);
        if (null === $user->getConfirmationToken()) {
            $user->setConfirmationToken($this->tokenGenerator->generateToken());
        }

        $this->userManager->updateUser($user);
        $this->mailer->sendConfirmationEmailMessage($user);

        /** @var NamespacedAttributeBag $sessionBag */
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
        /** @var UserInterface $user */
        $user = $event->getForm()->getData();

        $user->setPublished(false);
        if (null === $user->getConfirmationToken()) {
            $user->setConfirmationToken($this->tokenGenerator->generateToken());
        }

        $this->userManager->updateUser($user);
        $this->mailer->sendAdminNotificationEmailMessage($user);

        /** @var NamespacedAttributeBag $sessionBag */
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
        /** @var UserInterface $user */
        $user = $event->getForm()->getData();
        $user->setPublished(true);
        $this->userManager->updateUser($user);
    }
}
