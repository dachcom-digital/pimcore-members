<?php

namespace MembersBundle\EventListener\Maintenance;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Manager\SsoIdentityManagerInterface;
use Pimcore\Maintenance\TaskInterface;
use Pimcore\Model\DataObject\Concrete;

class SsoCleanUpExpiredTokenListener implements TaskInterface
{
    /**
     * @var bool
     */
    protected $oauthEnabled;

    /**
     * @var bool
     */
    protected $cleanUpExpiredTokens;

    /**
     * @var int
     */
    protected $expiredTokensTtl;

    /**
     * @var SsoIdentityManagerInterface
     */
    protected $ssoIdentityManager;

    /**
     * @param bool                        $oauthEnabled
     * @param bool                        $cleanUpExpiredTokens
     * @param int                         $expiredTokensTtl
     * @param SsoIdentityManagerInterface $ssoIdentityManager
     */
    public function __construct(
        bool $oauthEnabled,
        bool $cleanUpExpiredTokens,
        int $expiredTokensTtl,
        SsoIdentityManagerInterface $ssoIdentityManager
    ) {
        $this->oauthEnabled = $oauthEnabled;
        $this->cleanUpExpiredTokens = $cleanUpExpiredTokens;
        $this->expiredTokensTtl = $expiredTokensTtl;
        $this->ssoIdentityManager = $ssoIdentityManager;
    }

    /**
     * @throws \Exception
     */
    public function execute()
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
     * @param Concrete $ssoIdentity
     *
     * @throws \Exception
     */
    protected function handleIdentityRemoval(Concrete $ssoIdentity)
    {
        $user = $this->ssoIdentityManager->getUserBySsoIdentity($ssoIdentity->getProvider(), $ssoIdentity->getIdentifier());

        $ssoIdentity->delete();

        if (!$user instanceof UserInterface) {
            return;
        }

        // don't touch a user with a stored password
        if (!empty($user->getPassword())) {
            return;
        }

        // don't touch a user if he has other identities
        $userSsoIdentities = $this->ssoIdentityManager->getSsoIdentities($user);
        if (is_array($userSsoIdentities) && count($userSsoIdentities) > 0) {
            return;
        }

        if (!$user instanceof Concrete) {
            return;
        }

        $user->delete();
    }
}
