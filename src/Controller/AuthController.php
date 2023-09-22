<?php

namespace MembersBundle\Controller;

use MembersBundle\Form\Factory\FactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;

class AuthController extends AbstractController
{
    protected FactoryInterface $formFactory;

    public function __construct(FactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function loginAction(Request $request): Response
    {
        $authErrorKey = Security::AUTHENTICATION_ERROR;
        $lastUsernameKey = Security::LAST_USERNAME;

        $session = $request->getSession();

        // last username entered by the user
        $lastUsername = $session->get($lastUsernameKey);

        $targetPath = $request->get('_target_path', null);
        $failurePath = $request->get('_failure_path', null);

        $form = $this->formFactory->createUnnamedFormWithOptions([
            'last_username' => $lastUsername,
            '_target_path'  => $targetPath,
            '_failure_path' => $failurePath
        ]);

        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has($authErrorKey)) {
            $error = $request->attributes->get($authErrorKey);
        } elseif ($session->has($authErrorKey)) {
            $error = $session->get($authErrorKey);
            $session->remove($authErrorKey);
        } else {
            $error = null;
        }

        if (!$error instanceof AuthenticationException) {
            $error = null; // The value does not come from the security component.
        }

        return $this->render('@Members/auth/login.html.twig', [
            'form'          => $form,
            'last_username' => $lastUsername,
            'error'         => $error
        ]);
    }

    public function checkAction(): void
    {
        throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
    }

    public function logoutAction(): void
    {
        throw new \RuntimeException('You must activate the logout in your security firewall configuration.');
    }
}
