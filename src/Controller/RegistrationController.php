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
use MembersBundle\Event\GetResponseUserEvent;
use MembersBundle\Form\Factory\FactoryInterface;
use MembersBundle\Manager\UserManagerInterface;
use MembersBundle\MembersEvents;
use MembersBundle\Service\RequestPropertiesForUserExtractorServiceInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        protected FactoryInterface $formFactory,
        protected EventDispatcherInterface $eventDispatcher,
        protected UserManagerInterface $userManager,
        protected TokenStorageInterface $tokenStorage,
        protected RequestPropertiesForUserExtractorServiceInterface $requestPropertiesForUserExtractorService
    ) {
    }

    public function registerAction(Request $request): Response
    {
        $user = $this->userManager->createUser();

        $event = new GetResponseUserEvent($user, $request);
        $this->eventDispatcher->dispatch($event, MembersEvents::REGISTRATION_INITIALIZE);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $this->formFactory->createForm();

        $form->setData($user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->userManager->updateUser($user, $this->requestPropertiesForUserExtractorService->extract($request));

                $event = new FormEvent($form, $request);
                $this->eventDispatcher->dispatch($event, MembersEvents::REGISTRATION_SUCCESS);

                if (null === $response = $event->getResponse()) {
                    $url = $this->generateUrl('members_user_registration_confirmed');
                    $response = new RedirectResponse($url);
                }

                $event = new FilterUserResponseEvent($user, $request, $response);
                $this->eventDispatcher->dispatch($event, MembersEvents::REGISTRATION_COMPLETED);

                return $response;
            }

            $event = new FormEvent($form, $request);
            $this->eventDispatcher->dispatch($event, MembersEvents::REGISTRATION_FAILURE);

            if (null !== $response = $event->getResponse()) {
                return $response;
            }
        }

        return $this->render('@Members/registration/register.html.twig', ['form' => $form]);
    }

    public function checkEmailAction(Request $request): Response
    {
        /** @var AttributeBagInterface $sessionBag */
        $sessionBag = $request->getSession()->getBag('members_session');

        $email = $sessionBag->get('members_user_send_confirmation_email/email');

        if (empty($email)) {
            return new RedirectResponse($this->container->get('router')->generate('members_user_registration_register'));
        }

        $sessionBag->remove('members_user_send_confirmation_email/email');
        $user = $this->userManager->findUserByEmail($email);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with email "%s" does not exist', $email));
        }

        return $this->renderTemplate('@Members/registration/check_email.html.twig', ['user' => $user]);
    }

    public function checkAdminAction(Request $request): Response
    {
        /** @var AttributeBagInterface $sessionBag */
        $sessionBag = $request->getSession()->getBag('members_session');

        $email = $sessionBag->get('members_user_send_confirmation_email/email');

        if (empty($email)) {
            return new RedirectResponse($this->container->get('router')->generate('members_user_registration_register'));
        }

        $sessionBag->remove('members_user_send_confirmation_email/email');
        $user = $this->userManager->findUserByEmail($email);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with email "%s" does not exist', $email));
        }

        return $this->renderTemplate('@Members/registration/check_admin.html.twig', ['user' => $user]);
    }

    public function confirmPreviewAction(Request $request, string $token): Response
    {
        $user = $this->userManager->findUserByConfirmationToken($token);

        if ($user === null) {
            throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
        }

        $confirmationUrl = $this->generateUrl('members_user_registration_confirm', ['token' => $user->getConfirmationToken()]);

        return $this->renderTemplate('@Members/registration/confirm_preview.html.twig', [
            'user'            => $user,
            'confirmationUrl' => $confirmationUrl,
        ]);
    }

    public function confirmAction(Request $request, string $token): Response
    {
        $user = $this->userManager->findUserByConfirmationToken($token);

        if ($user === null) {
            return $this->renderTemplate('@Members/registration/confirmed.html.twig', [
                'tokenFound' => false,
                'user'       => null,
                'targetUrl'  => null
            ]);
        }

        $user->setConfirmationToken(null);
        $user->setPublished(true);

        $event = new GetResponseUserEvent($user, $request);
        $this->eventDispatcher->dispatch($event, MembersEvents::REGISTRATION_CONFIRM);

        $this->userManager->updateUser($user);

        if (null === $response = $event->getResponse()) {
            $url = $this->generateUrl('members_user_registration_confirmed');
            $response = new RedirectResponse($url);
        }

        $this->eventDispatcher->dispatch(new FilterUserResponseEvent($user, $request, $response), MembersEvents::REGISTRATION_CONFIRMED);

        return $response;
    }

    public function confirmedAction(Request $request): Response
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $session = $request->getSession()->getBag('members_session');

        return $this->renderTemplate('@Members/registration/confirmed.html.twig', [
            'tokenFound' => true,
            'user'       => $user,
            'targetUrl'  => $this->getTargetUrlFromSession($session)
        ]);
    }

    private function getTargetUrlFromSession(AttributeBagInterface $session): ?string
    {
        $token = $this->tokenStorage->getToken();

        if (!$token instanceof UsernamePasswordToken) {
            return null;
        }

        $key = sprintf('_security.%s.target_path', $token->getFirewallName());

        if ($session->has($key)) {
            return $session->get($key);
        }

        return null;
    }
}
