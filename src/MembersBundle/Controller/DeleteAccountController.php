<?php

namespace MembersBundle\Controller;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Event\FilterUserResponseEvent;
use MembersBundle\Event\GetResponseUserEvent;
use MembersBundle\Form\Factory\FactoryInterface;
use MembersBundle\Manager\UserManagerInterface;
use MembersBundle\MembersEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DeleteAccountController extends AbstractController
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

    public function deleteAccountAction(Request $request): Response
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $event = new GetResponseUserEvent($user, $request);
        $this->eventDispatcher->dispatch($event, MembersEvents::DELETE_ACCOUNT_INITIALIZE);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $this->formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = new GetResponseUserEvent($user, $request);
            $this->eventDispatcher->dispatch($event, MembersEvents::DELETE_ACCOUNT_SUCCESS);

            $this->userManager->deleteUser($user);

            if (null === $response = $event->getResponse()) {
                $url = $this->generateUrl('members_user_security_logout');
                $response = new RedirectResponse($url);
            }

            $this->eventDispatcher->dispatch(new FilterUserResponseEvent($user, $request, $response), MembersEvents::DELETE_ACCOUNT_COMPLETED);

            return $response;
        }

        return $this->renderTemplate('@Members/DeleteAccount/delete_account.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
