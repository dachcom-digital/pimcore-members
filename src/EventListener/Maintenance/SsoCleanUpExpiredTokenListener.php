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

namespace MembersBundle\EventListener\Maintenance;

use MembersBundle\Adapter\Sso\SsoIdentityInterface;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Manager\SsoIdentityManagerInterface;
use MembersBundle\Service\SsoIdentityStatusServiceInterface;
use Pimcore\Maintenance\TaskInterface;
use Pimcore\Model\DataObject\Concrete;

class SsoCleanUpExpiredTokenListener implements TaskInterface
{
    public function __construct(
        protected bool $oauthEnabled,
        protected bool $cleanUpExpiredTokens,
        protected int $expiredTokensTtl,
        protected SsoIdentityManagerInterface $ssoIdentityManager,
        protected SsoIdentityStatusServiceInterface $ssoIdentityStatusService
    ) {
    }

    /**
     * @throws \Exception
     */
    public function execute(): void
    {
        if ($this->oauthEnabled === false) {
            return;
        }

        if ($this->cleanUpExpiredTokens === false) {
            return;
        }

        $identities = $this->ssoIdentityManager->findExpiredSsoIdentities($this->expiredTokensTtl);

        foreach ($identities as $ssoIdentity) {
            if (!$ssoIdentity instanceof Concrete) {
                continue;
            }

            $this->handleIdentityRemoval($ssoIdentity);
        }
    }

    /**
     * @throws \Exception
     */
    protected function handleIdentityRemoval(SsoIdentityInterface $ssoIdentity): void
    {
        $user = $this->ssoIdentityManager->getUserBySsoIdentity($ssoIdentity->getProvider(), $ssoIdentity->getIdentifier());

        if (!$ssoIdentity instanceof Concrete) {
            return;
        }

        $ssoIdentity->delete();

        if (!$user instanceof UserInterface) {
            return;
        }

        if (!$user instanceof Concrete) {
            return;
        }

        if ($this->ssoIdentityStatusService->identityCanBeDeleted($user) === true) {
            $user->delete();
        }
    }
}
