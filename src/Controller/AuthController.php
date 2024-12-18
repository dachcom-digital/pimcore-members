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

use MembersBundle\Form\Factory\FactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

class AuthController extends AbstractController
{
    public function __construct(protected FactoryInterface $formFactory)
    {
    }

    public function loginAction(Request $request): Response
    {
        $authErrorKey = SecurityRequestAttributes::AUTHENTICATION_ERROR;
        $lastUsernameKey = SecurityRequestAttributes::LAST_USERNAME;

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
