<?php

namespace MembersBundle\Controller;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Event\FilterUserResponseEvent;
use MembersBundle\Event\FormEvent;
use MembersBundle\Event\GetResponseUserEvent;
use MembersBundle\Form\Factory\FactoryInterface;
use MembersBundle\Manager\UserManagerInterface;
use MembersBundle\MembersEvents;
use MembersBundle\Security\OAuth\OAuthRegistrationHandler;
use MembersBundle\Security\OAuth\OAuthResponseInterface;
use Pimcore\Http\Request\Resolver\SiteResolver;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RegistrationController extends AbstractController
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
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var SiteResolver
     */
    protected $siteResolver;

    /**
     * @var OAuthRegistrationHandler
     */
    protected $oAuthHandler;

    /**
     * @param FactoryInterface         $formFactory
     * @param EventDispatcherInterface $eventDispatcher
     * @param UserManagerInterface     $userManager
     * @param TokenStorageInterface    $tokenStorage
     * @param SiteResolver             $siteResolver
     * @param OAuthRegistrationHandler $oAuthHandler
     */
    public function __construct(
        FactoryInterface $formFactory,
        EventDispatcherInterface $eventDispatcher,
        UserManagerInterface $userManager,
        TokenStorageInterface $tokenStorage,
        SiteResolver $siteResolver,
        OAuthRegistrationHandler $oAuthHandler
    ) {
        $this->formFactory = $formFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->userManager = $userManager;
        $this->tokenStorage = $tokenStorage;
        $this->siteResolver = $siteResolver;
        $this->oAuthHandler = $oAuthHandler;
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response|null
     * @throws \Exception
     */
    public function registerAction(Request $request)
    {
        /** @var UserInterface $user */
        $user = $this->userManager->createUser();

        $event = new GetResponseUserEvent($user, $request);
        $this->eventDispatcher->dispatch(MembersEvents::REGISTRATION_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $registrationKey = $request->get('registrationKey', null);

        $oAuthResponse = null;

        // load previously stored token from the session and try to load user profile
        // from provider
        if ($registrationKey !== null) {
            $oAuthResponse = $this->oAuthHandler->loadToken($registrationKey);
        }

        if ($oAuthResponse instanceof OAuthResponseInterface) {
            if ($this->oAuthHandler->getCustomerFromUserResponse($oAuthResponse)) {
                throw new \RuntimeException('Customer is already registered');
            }

            $user = $this->mergeOAuthFormData($user, $oAuthResponse);
        }

        $formOptions = [];

        if ($oAuthResponse instanceof OAuthResponseInterface) {
            $formOptions['hide_password'] = $oAuthResponse instanceof OAuthResponseInterface;
            $formOptions['validation_groups'] = 'SSO';
        }

        $form = $this->formFactory->createUnnamedFormWithOptions($formOptions);

        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if ($form->isValid()) {
                $this->userManager->updateUser($user, $this->getUserProperties($request));

                // add SSO identity from OAuth data
                if ($oAuthResponse instanceof OAuthResponseInterface) {
                    $this->oAuthHandler->connectSsoIdentity($user, $oAuthResponse);
                }

                $event = new FormEvent($form, $request);
                $this->eventDispatcher->dispatch(MembersEvents::REGISTRATION_SUCCESS, $event);

                if (null === $response = $event->getResponse()) {
                    $url = $this->generateUrl('members_user_registration_confirmed');
                    $response = new RedirectResponse($url);
                }

                $event = new FilterUserResponseEvent($user, $request, $response);
                $this->eventDispatcher->dispatch(MembersEvents::REGISTRATION_COMPLETED, $event);

                return $response;
            }

            if ($registrationKey !== null) {
                $this->oAuthHandler->saveToken($registrationKey, $oAuthResponse);
            }

            $event = new FormEvent($form, $request);
            $this->eventDispatcher->dispatch(MembersEvents::REGISTRATION_FAILURE, $event);

            if (null !== $response = $event->getResponse()) {
                return $response;
            }
        }

        if ($registrationKey !== null) {
            $this->oAuthHandler->saveToken($registrationKey, $oAuthResponse);
        }

        return $this->renderTemplate('@Members/Registration/register.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function checkEmailAction(Request $request)
    {
        $sessionBag = $this->getMembersSessionBag($request);
        $email = $sessionBag->get('members_user_send_confirmation_email/email');

        if (empty($email)) {
            return new RedirectResponse($this->container->get('router')->generate('members_user_registration_register'));
        }

        $sessionBag->remove('members_user_send_confirmation_email/email');
        $user = $this->userManager->findUserByEmail($email);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with email "%s" does not exist', $email));
        }

        return $this->renderTemplate('@Members/Registration/check_email.html.twig', ['user' => $user]);
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function checkAdminAction(Request $request)
    {
        $sessionBag = $this->getMembersSessionBag($request);
        $email = $sessionBag->get('members_user_send_confirmation_email/email');

        if (empty($email)) {
            return new RedirectResponse($this->container->get('router')->generate('members_user_registration_register'));
        }

        $sessionBag->remove('members_user_send_confirmation_email/email');
        $user = $this->userManager->findUserByEmail($email);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with email "%s" does not exist', $email));
        }

        return $this->renderTemplate('@Members/Registration/check_admin.html.twig', ['user' => $user]);
    }

    /**
     * @param Request $request
     * @param string  $token
     *
     * @return null|RedirectResponse|Response
     */
    public function confirmAction(Request $request, $token)
    {
        /** @var UserInterface $user */
        $user = $this->userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
        }

        $user->setConfirmationToken(null);
        $user->setPublished(true);

        $event = new GetResponseUserEvent($user, $request);
        $this->eventDispatcher->dispatch(MembersEvents::REGISTRATION_CONFIRM, $event);

        $this->userManager->updateUser($user);

        if (null === $response = $event->getResponse()) {
            $url = $this->generateUrl('members_user_registration_confirmed');
            $response = new RedirectResponse($url);
        }

        $this->eventDispatcher->dispatch(MembersEvents::REGISTRATION_CONFIRMED, new FilterUserResponseEvent($user, $request, $response));

        return $response;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function confirmedAction(Request $request)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $session = $request->getSession()->getBag('members_session');

        return $this->renderTemplate('@Members/Registration/confirmed.html.twig', [
            'user'      => $user,
            'targetUrl' => $this->getTargetUrlFromSession($session)
        ]);
    }

    /**
     * @param SessionBagInterface $session
     *
     * @return null|string
     */
    private function getTargetUrlFromSession(SessionBagInterface $session)
    {
        $token = $this->tokenStorage->getToken();

        if (!$token instanceof UsernamePasswordToken) {
            return null;
        }

        $key = sprintf('_security.%s.target_path', $token->getProviderKey());

        if ($session->has($key)) {
            return $session->get($key);
        }

        return null;
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    private function getUserProperties($request)
    {
        $userProperties = [
            '_user_locale' => $request->getLocale()
        ];

        if ($this->siteResolver->isSiteRequest()) {
            $userProperties['_site_domain'] = $this->siteResolver->getSite($request)->getMainDomain();
        }

        return $userProperties;
    }

    /**
     * @param Request $request
     *
     * @return NamespacedAttributeBag
     */
    private function getMembersSessionBag(Request $request)
    {
        /** @var NamespacedAttributeBag $bag */
        $bag = $request->getSession()->getBag('members_session');

        return $bag;
    }

    /**
     * @param UserInterface          $user
     * @param OAuthResponseInterface $OAuthResponse
     *
     * @return UserInterface
     */
    private function mergeOAuthFormData(UserInterface $user, OAuthResponseInterface $OAuthResponse)
    {
        $userData = $OAuthResponse->getResourceOwner()->toArray();

        foreach (['firstname', 'lastname', 'userName', 'email'] as $field) {
            $setter = sprintf('set%s', ucfirst($field));
            if (isset($userData[$field])) {
                $user->$setter($userData[$field]);
            }
        }

        return $user;
    }
}
