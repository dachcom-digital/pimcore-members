<?php

namespace MembersBundle\Service;

use MembersBundle\Manager\SsoIdentityManagerInterface;
use MembersBundle\MembersEvents;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Event\OAuth\OAuthIdentityEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as ComponentEventDispatcherInterface;

class SsoIdentityStatusService implements SsoIdentityStatusServiceInterface
{
    protected SsoIdentityManagerInterface $ssoIdentityManager;
    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(
        SsoIdentityManagerInterface $ssoIdentityManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->ssoIdentityManager = $ssoIdentityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function identityCanCompleteProfile(UserInterface $user): bool
    {
        if ($this->eventDispatcher instanceof ComponentEventDispatcherInterface && $this->eventDispatcher->hasListeners(MembersEvents::OAUTH_IDENTITY_STATUS_PROFILE_COMPLETION) === false) {
            return $this->determinateProfileCompletionByDefaults($user);
        }

        $event = new OAuthIdentityEvent($user);
        $this->eventDispatcher->dispatch($event, MembersEvents::OAUTH_IDENTITY_STATUS_PROFILE_COMPLETION);

        return $event->identityCanDispatch();
    }

    public function identityCanBeDeleted(UserInterface $user): bool
    {
        if ($this->eventDispatcher instanceof ComponentEventDispatcherInterface && $this->eventDispatcher->hasListeners(MembersEvents::OAUTH_IDENTITY_STATUS_DELETION) === false) {
            return $this->determinateDeletionByDefaults($user);
        }

        $event = new OAuthIdentityEvent($user);
        $this->eventDispatcher->dispatch($event, MembersEvents::OAUTH_IDENTITY_STATUS_DELETION);

        return $event->identityCanDispatch();
    }

    protected function determinateProfileCompletionByDefaults(UserInterface $user): bool
    {
        return empty($user->getPassword());
    }

    protected function determinateDeletionByDefaults(UserInterface $user): bool
    {
        // don't touch a user with a stored password
        if (!empty($user->getPassword())) {
            return false;
        }

        // don't touch a user if he has other identities
        $userSsoIdentities = $this->ssoIdentityManager->getSsoIdentities($user);
        if (is_array($userSsoIdentities) && count($userSsoIdentities) > 0) {
            return false;
        }

        return true;
    }
}
