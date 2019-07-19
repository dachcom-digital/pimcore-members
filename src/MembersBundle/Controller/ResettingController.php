<?php

namespace MembersBundle\Controller;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Event\FilterUserResponseEvent;
use MembersBundle\Event\FormEvent;
use MembersBundle\Event\GetResponseNullableUserEvent;
use MembersBundle\Event\GetResponseUserEvent;
use MembersBundle\Form\Factory\FactoryInterface;
use MembersBundle\Mailer\MailerInterface;
use MembersBundle\Manager\UserManagerInterface;
use MembersBundle\MembersEvents;
use MembersBundle\Tool\TokenGeneratorInterface;
use Pimcore\Http\RequestHelper;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResettingController extends AbstractController
{
    /**
     * @var FactoryInterface
     */
    protected $requestResettingFormFactory;

    /**
     * @var FactoryInterface
     */
    protected $resettingFormFactory;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * @var TokenGeneratorInterface
     */
    protected $tokenGenerator;

    /**
     * @var MailerInterface
     */
    protected $mailer;

    /**
     * @var RequestHelper
     */
    protected $requestHelper;

    /**
     * @param FactoryInterface         $requestResettingFormFactory
     * @param FactoryInterface         $resettingFormFactory
     * @param EventDispatcherInterface $eventDispatcher
     * @param UserManagerInterface     $userManager
     * @param TokenGeneratorInterface  $tokenGenerator
     * @param MailerInterface          $mailer
     * @param RequestHelper            $requestHelper
     */
    public function __construct(
        FactoryInterface $requestResettingFormFactory,
        FactoryInterface $resettingFormFactory,
        EventDispatcherInterface $eventDispatcher,
        UserManagerInterface $userManager,
        TokenGeneratorInterface $tokenGenerator,
        MailerInterface $mailer,
        RequestHelper $requestHelper
    ) {
        $this->requestResettingFormFactory = $requestResettingFormFactory;
        $this->resettingFormFactory = $resettingFormFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->userManager = $userManager;
        $this->tokenGenerator = $tokenGenerator;
        $this->mailer = $mailer;
        $this->requestHelper = $requestHelper;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function requestAction(Request $request)
    {
        $form = $this->requestResettingFormFactory->createUnnamedForm();
        $form->handleRequest($request);

        $params = ['form' => $form->createView()];

        return $this->renderTemplate('@Members/Resetting/request.html.twig', $params);
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response|null
     *
     * @throws \Exception
     */
    public function sendEmailAction(Request $request)
    {
        $username = $request->request->get('username');

        /** @var UserInterface $user */
        $user = $this->userManager->findUserByUsernameOrEmail($username);

        /* Dispatch init event */
        $event = new GetResponseNullableUserEvent($user, $request);
        $this->eventDispatcher->dispatch(MembersEvents::RESETTING_SEND_EMAIL_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $ttl = $this->getParameter('members.resetting.retry_ttl');
        if ($user !== null && !$user->isPasswordRequestNonExpired($ttl)) {
            $event = new GetResponseUserEvent($user, $request);
            $this->eventDispatcher->dispatch(MembersEvents::RESETTING_RESET_REQUEST, $event);

            if (null !== $event->getResponse()) {
                return $event->getResponse();
            }

            if (null === $user->getConfirmationToken()) {
                $user->setConfirmationToken($this->tokenGenerator->generateToken());
            }

            /* Dispatch confirm event */
            $event = new GetResponseUserEvent($user, $request);
            $this->eventDispatcher->dispatch(MembersEvents::RESETTING_SEND_EMAIL_CONFIRM, $event);

            if (null !== $event->getResponse()) {
                return $event->getResponse();
            }

            $this->mailer->sendResettingEmailMessage($user);
            $user->setPasswordRequestedAt(new \Carbon\Carbon());
            $this->userManager->updateUser($user);

            /* Dispatch completed event */
            $event = new GetResponseUserEvent($user, $request);
            $this->eventDispatcher->dispatch(MembersEvents::RESETTING_SEND_EMAIL_COMPLETED, $event);

            if (null !== $event->getResponse()) {
                return $event->getResponse();
            }
        }

        return new RedirectResponse($this->generateUrl('members_user_resetting_check_email', ['username' => $username]));
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function checkEmailAction(Request $request)
    {
        $username = $request->query->get('username');

        if (empty($username)) {
            // the user does not come from the sendEmail action
            return new RedirectResponse($this->generateUrl('members_user_resetting_request'));
        }

        return $this->renderTemplate('@Members/Resetting/check_email.html.twig', [
            'tokenLifetime' => ceil($this->getParameter('members.resetting.retry_ttl') / 3600),
        ]);
    }

    /**
     * @param Request $request
     * @param string  $token
     *
     * @return null|RedirectResponse|Response
     */
    public function resetAction(Request $request, $token = null)
    {
        if ($this->requestHelper->isFrontendRequestByAdmin($request)) {
            return $this->renderTemplate('@Members/Backend/frontend_request.html.twig');
        }

        $user = $this->userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with "confirmation token" does not exist for value "%s"', $token));
        }

        $event = new GetResponseUserEvent($user, $request);
        $this->eventDispatcher->dispatch(MembersEvents::RESETTING_RESET_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $this->resettingFormFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = new FormEvent($form, $request);
            $this->eventDispatcher->dispatch(MembersEvents::RESETTING_RESET_SUCCESS, $event);

            if (null === $response = $event->getResponse()) {
                $url = $this->generateUrl('members_user_profile_show');
                $response = new RedirectResponse($url);
            }

            $this->eventDispatcher->dispatch(
                MembersEvents::RESETTING_RESET_COMPLETED,
                new FilterUserResponseEvent($user, $request, $response)
            );

            return $response;
        }

        return $this->renderTemplate('@Members/Resetting/reset.html.twig', [
            'token' => $token,
            'form'  => $form->createView(),
        ]);
    }
}
