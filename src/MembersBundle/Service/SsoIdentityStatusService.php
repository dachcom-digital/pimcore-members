<?php

namespace MembersBundle\Service;

use MembersBundle\Manager\SsoIdentityManagerInterface;
use MembersBundle\MembersEvents;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Event\OAuth\OAuthIdentityEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SsoIdentityStatusService implements SsoIdentityStatusServiceInterface
{
    /**
     * @var SsoIdentityManagerInterface
     */
    protected $ssoIdentityManager;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param SsoIdentityManagerInterface $ssoIdentityManager
     * @param EventDispatcherInterface    $eventDispatcher
     */
    public function __construct(
        SsoIdentityManagerInterface $ssoIdentityManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->ssoIdentityManager = $ssoIdentityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function identityCanCompleteProfile(UserInterface $user)
    {
        if ($this->eventDispatcher->hasListeners(MembersEvents::OAUTH_IDENTITY_STATUS_PROFILE_COMPLETION) === false) {
            return $this->determinateProfileCompletionByDefaults($user);
        }

        $event = new OAuthIdentityEvent($user);
        $this->eventDispatcher->dispatch($event, MembersEvents::OAUTH_IDENTITY_STATUS_PROFILE_COMPLETION);

        return $event->identityCanDispatch();
    }

    /**
     * @param UserInterface $user
     *
     * @return bool
     */
    public function identityCanBeDeleted(UserInterface $user)
    {
        if ($this->eventDispatcher->hasListeners(MembersEvents::OAUTH_IDENTITY_STATUS_DELETION) === false) {
            return $this->determinateDeletionByDefaults($user);
        }

        $event = new OAuthIdentityEvent($user);
        $this->eventDispatcher->dispatch($event, MembersEvents::OAUTH_IDENTITY_STATUS_DELETION);

        return $event->identityCanDispatch();
    }

    /**
     * @param UserInterface $user
     *
     * @return bool
     */
    protected function determinateProfileCompletionByDefaults(UserInterface $user)
    {
        return empty($user->getPassword());
    }

    /**
     * @param UserInterface $user
     *
     * @return bool
     */
    protected function determinateDeletionByDefaults(UserInterface $user)
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
