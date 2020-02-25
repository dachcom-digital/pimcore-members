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
use MembersBundle\Service\RequestPropertiesForUserExtractorServiceInterface;
use MembersBundle\Service\SsoIdentityStatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class OAuthController extends AbstractController
{
    /**
     * @var FactoryInterface
     */
    protected $formFactory;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * @var ClientRegistry
     */
    protected $clientRegistry;

    /**
     * @var OAuthScopeAllocatorInterface
     */
    protected $scopeAllocator;

    /**
     * @var SsoIdentityStatusServiceInterface
     */
    protected $identityStatusService;

    /**
     * @var RequestPropertiesForUserExtractorServiceInterface
     */
    protected $requestPropertiesForUserExtractorService;

    /**
     * @param FactoryInterface                                  $formFactory
     * @param EventDispatcherInterface                          $eventDispatcher
     * @param UserManagerInterface                              $userManager
     * @param ClientRegistry                                    $clientRegistry
     * @param OAuthScopeAllocatorInterface                      $scopeAllocator
     * @param SsoIdentityStatusServiceInterface                 $identityStatusService
     * @param RequestPropertiesForUserExtractorServiceInterface $requestPropertiesForUserExtractorService
     */
    public function __construct(
        FactoryInterface $formFactory,
        EventDispatcherInterface $eventDispatcher,
        UserManagerInterface $userManager,
        ClientRegistry $clientRegistry,
        OAuthScopeAllocatorInterface $scopeAllocator,
        SsoIdentityStatusServiceInterface $identityStatusService,
        RequestPropertiesForUserExtractorServiceInterface $requestPropertiesForUserExtractorService
    ) {
        $this->formFactory = $formFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->userManager = $userManager;
        $this->clientRegistry = $clientRegistry;
        $this->scopeAllocator = $scopeAllocator;
        $this->identityStatusService = $identityStatusService;
        $this->requestPropertiesForUserExtractorService = $requestPropertiesForUserExtractorService;
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function completeProfileAction(Request $request)
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

                $this->userManager->updateUser($user, $this->requestPropertiesForUserExtractorService->extract($request));

                $event = new FormEvent($form, $request);
                $this->eventDispatcher->dispatch(MembersEvents::OAUTH_SSO_INSTANCE_COMPLETE_PROFILE_SUCCESS, $event);

                if (null === $response = $event->getResponse()) {
                    $url = $this->generateUrl('members_user_sso_identity_profile_completed');
                    $response = new RedirectResponse($url);
                }

                $event = new FilterUserResponseEvent($user, $request, $response);
                $this->eventDispatcher->dispatch(MembersEvents::OAUTH_SSO_INSTANCE_COMPLETE_PROFILE_COMPLETED, $event);

                return $response;
            }

            $event = new FormEvent($form, $request);
            $this->eventDispatcher->dispatch(MembersEvents::OAUTH_SSO_INSTANCE_COMPLETE_PROFILE_FAILURE, $event);

            if (null !== $response = $event->getResponse()) {
                return $response;
            }
        }

        return $this->renderTemplate('@Members/Sso/CompleteProfile/complete_profile.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function profileCompletedAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $this->renderTemplate('@Members/Sso/CompleteProfile/completed.html.twig', ['user' => $user]);
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

        /** @var NamespacedAttributeBag $session */
        $session = $request->getSession()->getBag('members_session');
        $session->set('oauth_state_data', $params);

        $scopes = $this->scopeAllocator->allocate($provider);

        return $this->clientRegistry->getClient($provider)->redirect($scopes, []);
    }

    public function oAuthConnectCheckAction()
    {
        throw new \RuntimeException('You must activate the oauth guard authenticator in your security firewall configuration.');
    }
}
