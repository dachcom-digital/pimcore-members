<?php

namespace DachcomBundle\Test\unit\Config;

use DachcomBundle\Test\Test\DachcomBundleTestCase;
use MembersBundle\Configuration\Configuration;

class ConfigurationTest extends DachcomBundleTestCase
{
    /**
     * @throws \Codeception\Exception\ModuleException
     */
    public function testConfigArrayGetter()
    {
        $configuration = $this->getContainer()->get(Configuration::class);
        $adminConfig = $configuration->getConfigArray();

        $this->assertInternalType('array', $adminConfig);
        $this->assertArrayHasKey('send_admin_mail_after_register', $adminConfig);
    }

    /**
     * @throws \Codeception\Exception\ModuleException
     */
    public function testConfigSlotGetter()
    {
        $configuration = $this->getContainer()->get(Configuration::class);
        $configSlot = $configuration->getConfig('post_register_type');

        $this->assertInternalType('string', $configSlot);
    }
}
