<?php

namespace MembersBundle\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Event\FilterUserResponseEvent;
use MembersBundle\Event\FormEvent;
use MembersBundle\Form\Factory\FactoryInterface;
use MembersBundle\Manager\UserManagerInterface;
use MembersBundle\MembersEvents;
use MembersBundle\Security\OAuth\OAuthScopeAllocatorInterface;
use MembersBundle\Service\SsoIdentityStatusServiceInterface;
use Pimcore\Http\Request\Resolver\SiteResolver;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class OAuthController extends AbstractController
{
    protected FactoryInterface $formFactory;
    protected EventDispatcherInterface $eventDispatcher;
    protected UserManagerInterface $userManager;
    protected ClientRegistry $clientRegistry;
    protected OAuthScopeAllocatorInterface $scopeAllocator;
    protected SsoIdentityStatusServiceInterface $identityStatusService;

    public function __construct(
        FactoryInterface $formFactory,
        EventDispatcherInterface $eventDispatcher,
        UserManagerInterface $userManager,
        ClientRegistry $clientRegistry,
        OAuthScopeAllocatorInterface $scopeAllocator,
        SsoIdentityStatusServiceInterface $identityStatusService
    ) {
        $this->formFactory = $formFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->userManager = $userManager;
        $this->clientRegistry = $clientRegistry;
        $this->scopeAllocator = $scopeAllocator;
        $this->identityStatusService = $identityStatusService;
    }

    public function completeProfileAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var UserInterface $user */
        $user = $this->getUser();

        if ($this->identityStatusService->identityCanCompleteProfile($user) === false) {
            throw $this->createAccessDeniedException('Access Denied! Identity cannot complete profile (anymore).');
        }

        $form = $this->formFactory->createForm(['data' => $user]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->userManager->updateUser($user);

                $event = new FormEvent($form, $request);
                $this->eventDispatcher->dispatch($event, MembersEvents::OAUTH_SSO_INSTANCE_COMPLETE_PROFILE_SUCCESS);

                if (null === $response = $event->getResponse()) {
                    $url = $this->generateUrl('members_user_sso_identity_profile_completed');
                    $response = new RedirectResponse($url);
                }

                $event = new FilterUserResponseEvent($user, $request, $response);
                $this->eventDispatcher->dispatch($event, MembersEvents::OAUTH_SSO_INSTANCE_COMPLETE_PROFILE_COMPLETED);

                return $response;
            }

            $event = new FormEvent($form, $request);
            $this->eventDispatcher->dispatch($event, MembersEvents::OAUTH_SSO_INSTANCE_COMPLETE_PROFILE_FAILURE);

            if (null !== $response = $event->getResponse()) {
                return $response;
            }
        }

        return $this->renderTemplate('@Members/sso/complete-profile/complete_profile.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function profileCompletedAction(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $this->renderTemplate('@Members/sso/complete-profile/completed.html.twig', ['user' => $user]);
    }

    public function oAuthConnectAction(Request $request, string $provider): RedirectResponse
    {
        return $this->oAuthConnect($request, $provider, ['type' => 'login']);
    }

    public function oAuthProfileConnectAction(Request $request, string $provider): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        return $this->oAuthConnect($request, $provider, ['type' => 'connect']);
    }

    protected function oAuthConnect(Request $request, string $provider, array $params): RedirectResponse
    {
        $params = array_merge($params, [
            'provider'  => $provider,
            'parameter' => [
                'locale'      => $request->getLocale(),
                'target_path' => $request->get('_target_path', null),
                'site_id'     => $request->attributes->has(SiteResolver::ATTRIBUTE_SITE) ? $request->attributes->get(SiteResolver::ATTRIBUTE_SITE) : null
            ]
        ]);

        /** @var NamespacedAttributeBag $session */
        $session = $request->getSession()->getBag('members_session');
        $session->set('oauth_state_data', $params);

        $scopes = $this->scopeAllocator->allocate($provider);

        return $this->clientRegistry->getClient($provider)->redirect($scopes, []);
    }

    public function oAuthConnectCheckAction(): void
    {
        throw new \RuntimeException('You must activate the oauth guard authenticator in your security firewall configuration.');
    }
}
