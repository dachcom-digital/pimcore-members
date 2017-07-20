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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ProfileController extends AbstractController
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function showAction(Request $request)
    {
        if($this->container->get('pimcore.http.request_helper')->isFrontendRequestByAdmin($request)) {
            return $this->renderTemplate('@Members/Backend/frontend_request.html.twig');
        }

        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $this->renderTemplate('@Members/Profile/show.html.twig', ['user' => $user]);
    }

    /**
     * @param Request $request
     *
     * @return null|RedirectResponse|Response
     */
    public function editAction(Request $request)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        /** @var $dispatcher EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(MembersEvents::PROFILE_EDIT_INITIALIZE, $event);

        if (NULL !== $event->getResponse()) {
            return $event->getResponse();
        }

        /** @var $formFactory FactoryInterface */
        $formFactory = $this->get('members.profile.form.factory');

        $form = $formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var $userManager UserManager */
            $userManager = $this->get('members.manager.user');

            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(MembersEvents::PROFILE_EDIT_SUCCESS, $event);

            $userManager->updateUser($user);

            if (NULL === $response = $event->getResponse()) {
                $url = $this->generateUrl('members_user_profile_show');
                $response = new RedirectResponse($url);
            }

            $dispatcher->dispatch(MembersEvents::PROFILE_EDIT_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

            return $response;
        }

        return $this->renderTemplate('@Members/Profile/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function refusedAction(Request $request)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $this->renderTemplate('@Members/Profile/refused.html.twig', [
            'user' => $user,
        ]);
    }
}
