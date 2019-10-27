<?php

namespace MembersBundle\Controller;

use MembersBundle\Form\Factory\FactoryInterface;
use MembersBundle\Security\OAuth\OAuthRegistrationHandler;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;

class AuthController extends AbstractController
{
    /**
     * @var FactoryInterface
     */
    protected $formFactory;

    /**
     * @var OAuthRegistrationHandler
     */
    protected $oAuthHandler;

    /**
     * @param FactoryInterface         $formFactory
     * @param OAuthRegistrationHandler $oAuthHandler
     */
    public function __construct(
        FactoryInterface $formFactory,
        OAuthRegistrationHandler $oAuthHandler
    ) {
        $this->formFactory = $formFactory;
        $this->oAuthHandler = $oAuthHandler;
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function loginAction(Request $request)
    {
        $authErrorKey = Security::AUTHENTICATION_ERROR;
        $lastUsernameKey = Security::LAST_USERNAME;

        /** @var Session $session */
        $session = $request->getSession();

        // last username entered by the user
        $lastUsername = (null === $session) ? null : $session->get($lastUsernameKey);

        $form = $this->formFactory->createUnnamedFormWithOptions(['last_username' => $lastUsername]);

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

    /**
     * @param Request $request
     * @param string  $provider
     *
     * @return RedirectResponse
     */
    public function oAuthConnectAction(Request $request, string $provider)
    {
        return $this->oAuthConnect($request, $provider, ['type' => 'login']);
    }

    /**
     * @param Request $request
     * @param string  $provider
     *
     * @return RedirectResponse
     */
    public function oAuthProfileConnectAction(Request $request, string $provider)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        return $this->oAuthConnect($request, $provider, ['type' => 'connect']);
    }

    /**
     * @param Request $request
     * @param string  $provider
     * @param array   $params
     *
     * @return RedirectResponse
     */
    protected function oAuthConnect(Request $request, string $provider, array $params)
    {
        $params = array_merge($params, [
            '_target_path' => $request->get('_target_path', null),
            '_locale'      => $request->getLocale(),
            'provider'     => $provider
        ]);

        $clientRegistry = $this->get('knpu.oauth2.registry');
        $session = $request->getSession()->getBag('members_session');

        $session->set('oauth_state_data', $params);

        return $clientRegistry->getClient($provider)->redirect(['email']);
    }

    public function oAuthConnectCheckAction()
    {
        throw new \RuntimeException('You must activate the oauth guard authenticator in your security firewall configuration.');
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
