<?php

namespace MembersBundle\Controller;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Event\FilterUserResponseEvent;
use MembersBundle\Event\UserEvent;
use MembersBundle\Event\GetResponseUserEvent;
use MembersBundle\Form\Factory\FactoryInterface;
use MembersBundle\Manager\UserManager;
use MembersBundle\MembersEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DeleteAccountController extends AbstractController
{
    /**
     * @param Request $request
     *
     * @return null|RedirectResponse|Response
     */
    public function deleteAccountAction(Request $request)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        /** @var $dispatcher EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(MembersEvents::DELETE_ACCOUNT_INITIALIZE, $event);

        if (NULL !== $event->getResponse()) {
            return $event->getResponse();
        }

        /** @var $formFactory FactoryInterface */
        $formFactory = $this->get('members.delete_account.form.factory');

        $form = $formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var $userManager UserManager */
            $userManager = $this->get(UserManager::class);

            $event = new GetResponseUserEvent($user, $request);
            $dispatcher->dispatch(MembersEvents::DELETE_ACCOUNT_SUCCESS, $event);

            //$userManager->deleteUser($user);

            if (NULL === $response = $event->getResponse()) {
                $url = $this->generateUrl('members_user_security_logout');
                $response = new RedirectResponse($url);
            }

            $dispatcher->dispatch(MembersEvents::DELETE_ACCOUNT_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

            return $response;
        }

        return $this->renderTemplate('@Members/DeleteAccount/delete_account.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
