<?php

namespace MembersBundle\Controller;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Event\FilterUserResponseEvent;
use MembersBundle\Event\FormEvent;
use MembersBundle\Event\GetResponseUserEvent;
use MembersBundle\Form\Factory\FactoryInterface;
use MembersBundle\Manager\UserManagerInterface;
use MembersBundle\MembersEvents;
use Pimcore\Http\RequestHelper;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ProfileController extends AbstractController
{
    protected FactoryInterface $formFactory;
    protected EventDispatcherInterface $eventDispatcher;
    protected UserManagerInterface $userManager;
    protected RequestHelper $requestHelper;

    public function __construct(
        FactoryInterface $formFactory,
        EventDispatcherInterface $eventDispatcher,
        UserManagerInterface $userManager,
        RequestHelper $requestHelper
    ) {
        $this->formFactory = $formFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->userManager = $userManager;
        $this->requestHelper = $requestHelper;
    }

    public function showAction(Request $request): Response
    {
        if ($this->requestHelper->isFrontendRequestByAdmin($request)) {
            return $this->renderTemplate('@Members/backend/frontend_request.html.twig');
        }

        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $this->renderTemplate('@Members/profile/show.html.twig', ['user' => $user]);
    }

    public function editAction(Request $request): Response
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $event = new GetResponseUserEvent($user, $request);
        $this->eventDispatcher->dispatch($event, MembersEvents::PROFILE_EDIT_INITIALIZE);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $this->formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = new FormEvent($form, $request);
            $this->eventDispatcher->dispatch($event, MembersEvents::PROFILE_EDIT_SUCCESS);

            $this->userManager->updateUser($user);

            if (null === $response = $event->getResponse()) {
                $url = $this->generateUrl('members_user_profile_show');
                $response = new RedirectResponse($url);
            }

            $this->eventDispatcher->dispatch(new FilterUserResponseEvent($user, $request, $response), MembersEvents::PROFILE_EDIT_COMPLETED);

            return $response;
        }

        return $this->renderTemplate('@Members/profile/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function refusedAction(Request $request)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $this->renderTemplate('@Members/profile/refused.html.twig', [
            'user' => $user,
        ]);
    }
}
