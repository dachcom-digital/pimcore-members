<?php

namespace DachcomBundle\Test\Unit\Config;

use DachcomBundle\Test\Support\Test\DachcomBundleTestCase;
use MembersBundle\Configuration\Configuration;

class ConfigurationTest extends DachcomBundleTestCase
{
    public function testConfigArrayGetter(): void
    {
        $configuration = $this->getContainer()->get(Configuration::class);
        $adminConfig = $configuration->getConfigArray();

        $this->assertIsArray($adminConfig);
        $this->assertArrayHasKey('send_admin_mail_after_register', $adminConfig);
    }

    public function testConfigSlotGetter(): void
    {
        $configuration = $this->getContainer()->get(Configuration::class);
        $configSlot = $configuration->getConfig('post_register_type');

        $this->assertIsString($configSlot);
    }
}
