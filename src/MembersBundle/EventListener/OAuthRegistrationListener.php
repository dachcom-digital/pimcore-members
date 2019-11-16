<?php

namespace MembersBundle\EventListener;

use MembersBundle\Event\FilterUserResponseEvent;
use MembersBundle\Event\GetResponseUserEvent;
use MembersBundle\MembersEvents;
use MembersBundle\Security\OAuth\OAuthRegistrationHandler;
use MembersBundle\Security\OAuth\OAuthResponseInterface;
use MembersBundle\Security\OAuth\OAuthTokenStorageInterface;
use MembersBundle\Service\ResourceMappingService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;

class OAuthRegistrationListener implements EventSubscriberInterface
{
    /**
     * @var OAuthRegistrationHandler
     */
    protected $oAuthRegistrationHandler;

    /**
     * @var OAuthTokenStorageInterface
     */
    protected $oAuthTokenStorage;

    /**
     * @var ResourceMappingService
     */
    protected $resourceMappingService;

    /**
     * @param OAuthRegistrationHandler   $oAuthRegistrationHandler
     * @param OAuthTokenStorageInterface $oAuthTokenStorage
     * @param ResourceMappingService     $resourceMappingService
     */
    public function __construct(
        OAuthRegistrationHandler $oAuthRegistrationHandler,
        OAuthTokenStorageInterface $oAuthTokenStorage,
        ResourceMappingService $resourceMappingService
    ) {
        $this->oAuthRegistrationHandler = $oAuthRegistrationHandler;
        $this->oAuthTokenStorage = $oAuthTokenStorage;
        $this->resourceMappingService = $resourceMappingService;
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
        $oAuthResponse = $this->getOAuthResponse($request);

        if (!$oAuthResponse instanceof OAuthResponseInterface) {
            return;
        }

        if ($this->oAuthRegistrationHandler->getUserFromUserResponse($oAuthResponse)) {
            throw new \RuntimeException('User is already registered');
        }

        $request->attributes->set('_members_sso_aware', true);

        try {
            $this->resourceMappingService->mapResourceData($user, $oAuthResponse->getResourceOwner(), ResourceMappingService::MAP_FOR_REGISTRATION);
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException($e->getMessage());
        }
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
        $oAuthResponse = $this->getOAuthResponse($request, true);

        if (!$oAuthResponse instanceof OAuthResponseInterface) {
            return;
        }

        $this->oAuthRegistrationHandler->connectSsoIdentity($user, $oAuthResponse);
    }

    /**
     * @param Request $request
     * @param bool    $destroyToken
     *
     * @return OAuthResponseInterface|null
     */
    protected function getOAuthResponse(Request $request, bool $destroyToken = false)
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
}
