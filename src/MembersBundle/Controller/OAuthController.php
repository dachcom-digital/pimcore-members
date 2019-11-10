<?php

namespace MembersBundle\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class OAuthController extends AbstractController
{
    /**
     * @var ClientRegistry
     */
    protected $clientRegistry;

    /**
     * @param ClientRegistry $clientRegistry
     */
    public function __construct(ClientRegistry $clientRegistry)
    {
        $this->clientRegistry = $clientRegistry;
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

        $session = $request->getSession()->getBag('members_session');

        $session->set('oauth_state_data', $params);

        return $this->clientRegistry->getClient($provider)->redirect(['email'], []);
    }

    public function oAuthConnectCheckAction()
    {
        throw new \RuntimeException('You must activate the oauth guard authenticator in your security firewall configuration.');
    }
}
