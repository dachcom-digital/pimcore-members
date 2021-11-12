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
    protected OAuthRegistrationHandler $oAuthRegistrationHandler;
    protected OAuthTokenStorageInterface $oAuthTokenStorage;
    protected ResourceMappingService $resourceMappingService;

    public function __construct(
        OAuthRegistrationHandler $oAuthRegistrationHandler,
        OAuthTokenStorageInterface $oAuthTokenStorage,
        ResourceMappingService $resourceMappingService
    ) {
        $this->oAuthRegistrationHandler = $oAuthRegistrationHandler;
        $this->oAuthTokenStorage = $oAuthTokenStorage;
        $this->resourceMappingService = $resourceMappingService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MembersEvents::REGISTRATION_INITIALIZE => 'onRegistrationInitialization',
            MembersEvents::REGISTRATION_COMPLETED  => 'onRegistrationComplete'
        ];
    }

    public function onRegistrationInitialization(GetResponseUserEvent $event): void
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
     * @throws \Exception
     */
    public function onRegistrationComplete(FilterUserResponseEvent $event): void
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

    protected function getOAuthResponse(Request $request, bool $destroyToken = false): ?OAuthResponseInterface
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
