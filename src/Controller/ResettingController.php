<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

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
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ResettingController extends AbstractController
{
    public function __construct(
        protected FactoryInterface $requestResettingFormFactory,
        protected FactoryInterface $resettingFormFactory,
        protected EventDispatcherInterface $eventDispatcher,
        protected UserManagerInterface $userManager,
        protected TokenGeneratorInterface $tokenGenerator,
        protected MailerInterface $mailer,
        protected RequestHelper $requestHelper
    ) {
    }

    public function requestAction(Request $request): Response
    {
        $form = $this->requestResettingFormFactory->createUnnamedForm();
        $form->handleRequest($request);

        return $this->render('@Members/resetting/request.html.twig', ['form' => $form]);
    }

    public function sendEmailAction(Request $request): Response
    {
        $username = $request->request->get('username');

        /** @var UserInterface|null $user */
        $user = $this->userManager->findUserByUsernameOrEmail($username);

        /* Dispatch init event */
        $event = new GetResponseNullableUserEvent($user, $request);
        $this->eventDispatcher->dispatch($event, MembersEvents::RESETTING_SEND_EMAIL_INITIALIZE);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $ttl = $this->getParameter('members.resetting.retry_ttl');
        if ($user !== null && !$user->isPasswordRequestNonExpired($ttl)) {
            $event = new GetResponseUserEvent($user, $request);
            $this->eventDispatcher->dispatch($event, MembersEvents::RESETTING_RESET_REQUEST);

            if (null !== $event->getResponse()) {
                return $event->getResponse();
            }

            if (null === $user->getConfirmationToken()) {
                $user->setConfirmationToken($this->tokenGenerator->generateToken());
            }

            /* Dispatch confirm event */
            $event = new GetResponseUserEvent($user, $request);
            $this->eventDispatcher->dispatch($event, MembersEvents::RESETTING_SEND_EMAIL_CONFIRM);

            if (null !== $event->getResponse()) {
                return $event->getResponse();
            }

            $this->mailer->sendResettingEmailMessage($user);
            $user->setPasswordRequestedAt(new \Carbon\Carbon());
            $this->userManager->updateUser($user);

            /* Dispatch completed event */
            $event = new GetResponseUserEvent($user, $request);
            $this->eventDispatcher->dispatch($event, MembersEvents::RESETTING_SEND_EMAIL_COMPLETED);

            if (null !== $event->getResponse()) {
                return $event->getResponse();
            }
        }

        return new RedirectResponse($this->generateUrl('members_user_resetting_check_email', ['username' => $username]));
    }

    public function checkEmailAction(Request $request): Response
    {
        $username = $request->query->get('username');

        if (empty($username)) {
            // the user does not come from the sendEmail action
            return new RedirectResponse($this->generateUrl('members_user_resetting_request'));
        }

        return $this->renderTemplate('@Members/resetting/check_email.html.twig', [
            'tokenLifetime' => ceil($this->getParameter('members.resetting.retry_ttl') / 3600),
        ]);
    }

    public function resetAction(Request $request, ?string $token = null): Response
    {
        if ($this->requestHelper->isFrontendRequestByAdmin($request)) {
            return $this->renderTemplate('@Members/backend/frontend_request.html.twig');
        }

        $user = $this->userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with "confirmation token" does not exist for value "%s"', $token));
        }

        $event = new GetResponseUserEvent($user, $request);
        $this->eventDispatcher->dispatch($event, MembersEvents::RESETTING_RESET_INITIALIZE);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $this->resettingFormFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = new FormEvent($form, $request);
            $this->eventDispatcher->dispatch($event, MembersEvents::RESETTING_RESET_SUCCESS);

            if (null === $response = $event->getResponse()) {
                $url = $this->generateUrl('members_user_profile_show');
                $response = new RedirectResponse($url);
            }

            $this->eventDispatcher->dispatch(new FilterUserResponseEvent($user, $request, $response), MembersEvents::RESETTING_RESET_COMPLETED);

            return $response;
        }

        return $this->render('@Members/resetting/reset.html.twig', [
            'token' => $token,
            'form'  => $form,
        ]);
    }
}
