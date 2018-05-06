<?php

namespace MembersBundle\Controller;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Event\FilterUserResponseEvent;
use MembersBundle\Event\FormEvent;
use MembersBundle\Event\GetResponseUserEvent;
use MembersBundle\Form\Factory\FactoryInterface;
use MembersBundle\Manager\UserManager;
use MembersBundle\MembersEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RegistrationController extends AbstractController
{
    /**
     * @param Request $request
     * @return null|RedirectResponse|Response
     */
    public function registerAction(Request $request)
    {
        /** @var $formFactory FactoryInterface */
        $formFactory = $this->get('members.registration.form.factory');

        /** @var $userManager UserManager */
        $userManager = $this->get(UserManager::class);

        /** @var $dispatcher EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        /** @var UserInterface $user */
        $user = $userManager->createUser();

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(MembersEvents::REGISTRATION_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if ($form->isValid()) {

                $userManager->updateUser($user, $this->getUserProperties($request));

                $event = new FormEvent($form, $request);
                $dispatcher->dispatch(MembersEvents::REGISTRATION_SUCCESS, $event);

                if (null === $response = $event->getResponse()) {
                    $url = $this->generateUrl('members_user_registration_confirmed');
                    $response = new RedirectResponse($url);
                }

                $event = new FilterUserResponseEvent($user, $request, $response);
                $dispatcher->dispatch(MembersEvents::REGISTRATION_COMPLETED, $event);

                return $response;
            }

            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(MembersEvents::REGISTRATION_FAILURE, $event);

            if (null !== $response = $event->getResponse()) {
                return $response;
            }
        }

        return $this->renderTemplate('@Members/Registration/register.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @return RedirectResponse|Response
     */
    public function checkEmailAction()
    {
        $sessionBag = $this->get('session')->getBag('members_session');
        $email = $sessionBag->get('members_user_send_confirmation_email/email');

        if (empty($email)) {
            return new RedirectResponse($this->get('router')->generate('members_user_registration_register'));
        }

        $sessionBag->remove('members_user_send_confirmation_email/email');
        $user = $this->get(UserManager::class)->findUserByEmail($email);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with email "%s" does not exist', $email));
        }

        return $this->renderTemplate('@Members/Registration/check_email.html.twig', ['user' => $user]);
    }

    /**
     * @return RedirectResponse|Response
     */
    public function checkAdminAction()
    {
        $sessionBag = $this->get('session')->getBag('members_session');
        $email = $sessionBag->get('members_user_send_confirmation_email/email');

        if (empty($email)) {
            return new RedirectResponse($this->get('router')->generate('members_user_registration_register'));
        }

        $sessionBag->remove('members_user_send_confirmation_email/email');
        $user = $this->get(UserManager::class)->findUserByEmail($email);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with email "%s" does not exist', $email));
        }

        return $this->renderTemplate('@Members/Registration/check_admin.html.twig', ['user' => $user]);

    }

    /**
     * @param Request $request
     * @param         $token
     * @return null|RedirectResponse|Response
     */
    public function confirmAction(Request $request, $token)
    {
        /** @var $userManager UserManager */
        $userManager = $this->get(UserManager::class);

        /** @var UserInterface $user */
        $user = $userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
        }

        /** @var $dispatcher EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $user->setConfirmationToken(null);
        $user->setPublished(true);

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(MembersEvents::REGISTRATION_CONFIRM, $event);

        $userManager->updateUser($user);

        if (null === $response = $event->getResponse()) {
            $url = $this->generateUrl('members_user_registration_confirmed');
            $response = new RedirectResponse($url);
        }

        $dispatcher->dispatch(MembersEvents::REGISTRATION_CONFIRMED, new FilterUserResponseEvent($user, $request, $response));

        return $response;
    }

    /**
     * @return Response
     */
    public function confirmedAction()
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $this->renderTemplate('@Members/Registration/confirmed.html.twig', [
            'user'      => $user,
            'targetUrl' => $this->getTargetUrlFromSession()
        ]);
    }

    /**
     * @return mixed
     */
    private function getTargetUrlFromSession()
    {
        $key = sprintf('_security.%s.target_path', $this->get('security.token_storage')->getToken()->getProviderKey());

        if ($this->get('session')->has($key)) {
            return $this->get('session')->get($key);
        }
    }

    /**
     * @param Request $request
     * @return array
     */
    private function getUserProperties($request)
    {
        $siteResolver = $this->get('pimcore.service.request.site_resolver');

        $userProperties = [
            '_user_locale' => $request->getLocale()
        ];

        if ($siteResolver->isSiteRequest()) {
            $userProperties['_site_domain'] = $siteResolver->getSite($request)->getMainDomain();
        }

        return $userProperties;
    }
}