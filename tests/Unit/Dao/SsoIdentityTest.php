<?php

namespace DachcomBundle\Test\unit\Dao;

use DachcomBundle\Test\Test\DachcomBundleTestCase;
use Pimcore\Model\DataObject\SsoIdentity;

class SsoIdentityTest extends DachcomBundleTestCase
{
    /**
     * @throws \Exception
     */
    public function testSsoIdentityDaoEntity()
    {
        $ssoIdentity = $this->createSsoIdentity(true, 'google', '1234');

        $storedUser = SsoIdentity::getById($ssoIdentity->getId(), ['force' => true]);

        $this->assertInstanceOf(SsoIdentity::class, $storedUser);
        $this->assertEquals('google', $storedUser->getProvider());
        $this->assertEquals('1234', $storedUser->getIdentifier());
        $this->assertEquals(null, $storedUser->getProfileData());
        $this->assertEquals(null, $storedUser->getAccessToken());
        $this->assertEquals(null, $storedUser->getTokenType());
        $this->assertEquals(null, $storedUser->getExpiresAt());
        $this->assertEquals(null, $storedUser->getRefreshToken());
        $this->assertEquals(null, $storedUser->getScope());
    }
}
