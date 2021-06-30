<?php

namespace MembersBundle\EventListener\Maintenance;

use Pimcore\Maintenance\TaskInterface;
use Pimcore\Model\DataObject\Concrete;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Adapter\Sso\SsoIdentityInterface;
use MembersBundle\Manager\SsoIdentityManagerInterface;
use MembersBundle\Service\SsoIdentityStatusServiceInterface;

class SsoCleanUpExpiredTokenListener implements TaskInterface
{
    protected bool $oauthEnabled;
    protected bool $cleanUpExpiredTokens;
    protected int $expiredTokensTtl;
    protected SsoIdentityManagerInterface $ssoIdentityManager;
    protected SsoIdentityStatusServiceInterface $ssoIdentityStatusService;

    public function __construct(
        bool $oauthEnabled,
        bool $cleanUpExpiredTokens,
        int $expiredTokensTtl,
        SsoIdentityManagerInterface $ssoIdentityManager,
        SsoIdentityStatusServiceInterface $ssoIdentityStatusService
    ) {
        $this->oauthEnabled = $oauthEnabled;
        $this->cleanUpExpiredTokens = $cleanUpExpiredTokens;
        $this->expiredTokensTtl = $expiredTokensTtl;
        $this->ssoIdentityManager = $ssoIdentityManager;
        $this->ssoIdentityStatusService = $ssoIdentityStatusService;
    }

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
