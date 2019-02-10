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

class ChangePasswordController extends AbstractController
{
    /**
     * @param Request $request
     * @return null|RedirectResponse|Response
     */
    public function changePasswordAction(Request $request)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = $this->get('event_dispatcher');

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(MembersEvents::CHANGE_PASSWORD_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        /** @var FactoryInterface $formFactory */
        $formFactory = $this->get('members.change_password.form.factory');

        $form = $formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UserManager $userManager */
            $userManager = $this->get(UserManager::class);

            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(MembersEvents::CHANGE_PASSWORD_SUCCESS, $event);

            $userManager->updateUser($user);

            if (null === $response = $event->getResponse()) {
                $url = $this->generateUrl('members_user_profile_show');
                $response = new RedirectResponse($url);
            }

            $dispatcher->dispatch(MembersEvents::CHANGE_PASSWORD_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

            return $response;
        }

        return $this->renderTemplate('@Members/ChangePassword/change_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
