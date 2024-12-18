<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace MembersBundle\Service;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Event\OAuth\OAuthIdentityEvent;
use MembersBundle\Manager\SsoIdentityManagerInterface;
use MembersBundle\MembersEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as ComponentEventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SsoIdentityStatusService implements SsoIdentityStatusServiceInterface
{
    public function __construct(
        protected SsoIdentityManagerInterface $ssoIdentityManager,
        protected EventDispatcherInterface $eventDispatcher
    ) {
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

        return count($userSsoIdentities) <= 0;
    }
}
