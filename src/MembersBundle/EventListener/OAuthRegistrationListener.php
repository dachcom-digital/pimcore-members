<?php

namespace MembersBundle\EventListener;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Event\FilterUserResponseEvent;
use MembersBundle\Event\GetResponseUserEvent;
use MembersBundle\MembersEvents;
use MembersBundle\Security\OAuth\OAuthRegistrationHandler;
use MembersBundle\Security\OAuth\OAuthResponseInterface;
use MembersBundle\Security\OAuth\OAuthTokenStorageInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;

class OAuthRegistrationListener implements EventSubscriberInterface
{
    /**
     * @var OAuthRegistrationHandler
     */
    protected $oAuthHandler;

    /**
     * @var OAuthTokenStorageInterface
     */
    protected $oAuthTokenStorage;

    /**
     *
     * @param OAuthRegistrationHandler   $oAuthHandler
     * @param OAuthTokenStorageInterface $oAuthTokenStorage
     */
    public function __construct(
        OAuthRegistrationHandler $oAuthHandler,
        OAuthTokenStorageInterface $oAuthTokenStorage
    ) {
        $this->oAuthHandler = $oAuthHandler;
        $this->oAuthTokenStorage = $oAuthTokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            MembersEvents::REGISTRATION_INITIALIZE => 'onRegistrationInitialization',
            MembersEvents::REGISTRATION_COMPLETED  => 'onRegistrationComplete'
        ];
    }

    /**
     * @param GetResponseUserEvent $event
     */
    public function onRegistrationInitialization(GetResponseUserEvent $event)
    {
        $request = $event->getRequest();
        $user = $event->getUser();

        // load previously stored token from the session
        // and try to load user profile from provider
        $oAuthResponse = $this->getOAUthResponse($request);

        if (!$oAuthResponse instanceof OAuthResponseInterface) {
            return;
        }

        if ($this->oAuthHandler->getCustomerFromUserResponse($oAuthResponse)) {
            throw new \RuntimeException('Customer is already registered');
        }

        $request->attributes->set('_members_sso_aware', true);

        $this->mergeOAuthFormData($user, $oAuthResponse);

    }

    /**
     * @param FilterUserResponseEvent $event
     *
     * @throws \Exception
     */
    public function onRegistrationComplete(FilterUserResponseEvent $event)
    {
        $request = $event->getRequest();
        $user = $event->getUser();

        // load previously stored token from the session
        // and try to load user profile from provider
        $oAuthResponse = $this->getOAUthResponse($request, true);

        if (!$oAuthResponse instanceof OAuthResponseInterface) {
            return;
        }

        $this->oAuthHandler->connectSsoIdentity($user, $oAuthResponse);
    }

    /**
     * @param Request $request
     * @param bool    $destroyToken
     *
     * @return OAuthResponseInterface|null
     */
    protected function getOAUthResponse(Request $request, bool $destroyToken = false)
    {
        $registrationKey = $request->get('registrationKey', null);

        // load previously stored token from the session
        // and try to load user profile from provider
        if ($registrationKey === null) {
            return null;
        }

        $token = $this->oAuthTokenStorage->loadToken($registrationKey);

        if ($destroyToken === true) {
            $this->oAuthTokenStorage->destroyToken($registrationKey);
        }

        return $token;
    }

    /**
     * @param UserInterface          $user
     * @param OAuthResponseInterface $OAuthResponse
     *
     * @return UserInterface
     * @todo: move to resource mapping service
     */
    protected function mergeOAuthFormData(UserInterface $user, OAuthResponseInterface $OAuthResponse)
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
