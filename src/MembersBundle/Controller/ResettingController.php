<?php

namespace MembersBundle\Controller;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Event\FilterUserResponseEvent;
use MembersBundle\Event\FormEvent;
use MembersBundle\Event\GetResponseNullableUserEvent;
use MembersBundle\Event\GetResponseUserEvent;
use MembersBundle\Mailer\Mailer;
use MembersBundle\Manager\UserManager;
use MembersBundle\MembersEvents;
use MembersBundle\Tool\TokenGenerator;
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
     * @param Request $request
     * @return Response
     */
    public function requestAction(Request $request)
    {
        /** @var $formFactory \MembersBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('members.resetting_request.form.factory');

        $form = $formFactory->createUnnamedForm();
        $form->handleRequest($request);

        $params = ['form' => $form->createView()];

        return $this->renderTemplate('@Members/Resetting/request.html.twig', $params);
    }

    /**
     * @param Request $request
     * @return null|RedirectResponse|Response
     */
    public function sendEmailAction(Request $request)
    {
        $username = $request->request->get('username');

        /** @var $user UserInterface */
        $user = $this->get(UserManager::class)->findUserByUsernameOrEmail($username);

        /** @var $dispatcher EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        /* Dispatch init event */
        $event = new GetResponseNullableUserEvent($user, $request);
        $dispatcher->dispatch(MembersEvents::RESETTING_SEND_EMAIL_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $ttl = $this->getParameter('members.resetting.retry_ttl');
        if ($user !== null && !$user->isPasswordRequestNonExpired($ttl)) {

            $event = new GetResponseUserEvent($user, $request);
            $dispatcher->dispatch(MembersEvents::RESETTING_RESET_REQUEST, $event);

            if (null !== $event->getResponse()) {
                return $event->getResponse();
            }

            if (null === $user->getConfirmationToken()) {
                /** @var $tokenGenerator TokenGeneratorInterface */
                $tokenGenerator = $this->get(TokenGenerator::class);
                $user->setConfirmationToken($tokenGenerator->generateToken());
            }

            /* Dispatch confirm event */
            $event = new GetResponseUserEvent($user, $request);
            $dispatcher->dispatch(MembersEvents::RESETTING_SEND_EMAIL_CONFIRM, $event);

            if (null !== $event->getResponse()) {
                return $event->getResponse();
            }

            $this->get(Mailer::class)->sendResettingEmailMessage($user);
            $user->setPasswordRequestedAt(new \Carbon\Carbon());
            $this->get(UserManager::class)->updateUser($user);

            /* Dispatch completed event */
            $event = new GetResponseUserEvent($user, $request);
            $dispatcher->dispatch(MembersEvents::RESETTING_SEND_EMAIL_COMPLETED, $event);

            if (null !== $event->getResponse()) {
                return $event->getResponse();
            }
        }

        return new RedirectResponse($this->generateUrl('members_user_resetting_check_email', ['username' => $username]));
    }

    /**
     * @param Request $request
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
            'tokenLifetime' => ceil($this->container->getParameter('members.resetting.retry_ttl') / 3600),
        ]);
    }

    /**
     * @param Request $request
     * @param         $token
     * @return null|RedirectResponse|Response
     */
    public function resetAction(Request $request, $token = null)
    {
        if ($this->container->get(RequestHelper::class)->isFrontendRequestByAdmin($request)) {
            return $this->renderTemplate('@Members/Backend/frontend_request.html.twig');
        }

        /** @var $formFactory \MembersBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('members.resetting.form.factory');
        /** @var $userManager UserManager */
        $userManager = $this->get(UserManager::class);
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $user = $userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with "confirmation token" does not exist for value "%s"', $token));
        }

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(MembersEvents::RESETTING_RESET_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(MembersEvents::RESETTING_RESET_SUCCESS, $event);

            if (null === $response = $event->getResponse()) {
                $url = $this->generateUrl('members_user_profile_show');
                $response = new RedirectResponse($url);
            }

            $dispatcher->dispatch(
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
