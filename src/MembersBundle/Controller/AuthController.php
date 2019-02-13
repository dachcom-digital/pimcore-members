<?php

namespace MembersBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;

class AuthController extends AbstractController
{
    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function loginAction(Request $request)
    {
        $authErrorKey = Security::AUTHENTICATION_ERROR;
        $lastUsernameKey = Security::LAST_USERNAME;

        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $request->getSession();

        // last username entered by the user
        $lastUsername = (null === $session) ? null : $session->get($lastUsernameKey);

        $formFactory = $this->get('members.security.login.form.factory');
        $form = $formFactory->createUnnamedFormWithOptions(['last_username' => $lastUsername]);

        $form->handleRequest($request);

        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has($authErrorKey)) {
            $error = $request->attributes->get($authErrorKey);
        } elseif (null !== $session && $session->has($authErrorKey)) {
            $error = $session->get($authErrorKey);
            $session->remove($authErrorKey);
        } else {
            $error = null;
        }

        if (!$error instanceof AuthenticationException) {
            $error = null; // The value does not come from the security component.
        }

        $authParams = [
            'form'          => $form->createView(),
            'last_username' => $lastUsername,
            'error'         => $error
        ];

        return $this->renderTemplate('@Members/Auth/login.html.twig', $authParams);
    }

    public function checkAction()
    {
        throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
    }

    public function logoutAction()
    {
        throw new \RuntimeException('You must activate the logout in your security firewall configuration.');
    }
}
