<?php

namespace MembersBundle\EventListener\Maintenance;

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

    public function execute()
    {
        if ($this->oauthEnabled === false) {
            return;
        }

        if ($this->cleanUpExpiredTokens === false) {
            return;
        }

        $identities = $this->ssoIdentityManager->findExpiredSsoIdentities($this->expiredTokensTtl);

        foreach ($identities as $identity) {

            if (!$identity instanceof Concrete) {
                continue;
            }

            // @todo: also remove simple oauth user?
            $identity->delete();
        }
    }
}
