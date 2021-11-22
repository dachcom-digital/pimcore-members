<?php

namespace MembersBundle\Controller;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Event\FilterUserResponseEvent;
use MembersBundle\Event\FormEvent;
use MembersBundle\Event\GetResponseUserEvent;
use MembersBundle\Form\Factory\FactoryInterface;
use MembersBundle\Manager\UserManagerInterface;
use MembersBundle\MembersEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ChangePasswordController extends AbstractController
{
    protected FactoryInterface $formFactory;
    protected EventDispatcherInterface $eventDispatcher;
    protected UserManagerInterface $userManager;

    public function __construct(
        FactoryInterface $formFactory,
        EventDispatcherInterface $eventDispatcher,
        UserManagerInterface $userManager
    ) {
        $this->formFactory = $formFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->userManager = $userManager;
    }

    public function changePasswordAction(Request $request): Response
    {
        $user = $this->getUser();

        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $event = new GetResponseUserEvent($user, $request);
        $this->eventDispatcher->dispatch($event, MembersEvents::CHANGE_PASSWORD_INITIALIZE);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $this->formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = new FormEvent($form, $request);
            $this->eventDispatcher->dispatch($event, MembersEvents::CHANGE_PASSWORD_SUCCESS);

            $this->userManager->updateUser($user);

            if (null === $response = $event->getResponse()) {
                $url = $this->generateUrl('members_user_profile_show');
                $response = new RedirectResponse($url);
            }

            $this->eventDispatcher->dispatch(new FilterUserResponseEvent($user, $request, $response), MembersEvents::CHANGE_PASSWORD_COMPLETED);

            return $response;
        }

        return $this->renderTemplate('@Members/change-password/change_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
