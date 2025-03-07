<?php

namespace DachcomBundle\Test\Unit\Manager;

use DachcomBundle\Test\Support\Test\DachcomBundleTestCase;
use MembersBundle\Adapter\Sso\SsoIdentityInterface;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Manager\SsoIdentityManager;
use Pimcore\File;
use Pimcore\Model\DataObject\SsoIdentity;

class SsoIdentityManagerTest extends DachcomBundleTestCase
{
    public function testClassGetter(): void
    {
        $ssoIdentityManager = $this->getContainer()->get(SsoIdentityManager::class);
        $ssoIdentityClass = $ssoIdentityManager->getClass();

        $this->assertEquals(SsoIdentity::class, $ssoIdentityClass);
    }

    public function testCreateNewSsoIdentity(): void
    {
        $ssoIdentity = $this->createSsoIdentity(true, 'google', '1234');
        $this->assertInstanceOf(SsoIdentityInterface::class, $ssoIdentity);

        $expectedKey = File::getValidFilename(sprintf('%s-%s', 'google', '1234'));
        $this->assertEquals($expectedKey, $ssoIdentity->getKey());
    }

    public function testFindUserBySsoIdentity(): void
    {
        $ssoIdentity = $this->createSsoIdentity(true, 'google', '1234');
        $ssoIdentityManager = $this->getContainer()->get(SsoIdentityManager::class);

        $this->assertInstanceOf(
            UserInterface::class,
            $ssoIdentityManager->getUserBySsoIdentity($ssoIdentity->getProvider(), $ssoIdentity->getIdentifier())
        );
    }
}
