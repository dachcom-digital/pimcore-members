<?php

namespace DachcomBundle\Test\Unit\Security;

use DachcomBundle\Test\Support\Test\DachcomBundleTestCase;
use MembersBundle\CoreExtension\Provider\RoleOptionsProvider;
use Pimcore\Model\DataObject\ClassDefinition\Data\Multiselect;

class RoleOptionsProviderTest extends DachcomBundleTestCase
{
    /**
     * @throws \Codeception\Exception\ModuleException
     */
    public function testRoleOptions()
    {
        /** @var RoleOptionsProvider $roleOptionsProvider */
        $roleOptionsProvider = $this->getContainer()->get(RoleOptionsProvider::class);

        /** @var Multiselect $multiSelectMock */
        $multiSelectMock = $this->getMockBuilder(Multiselect::class)->getMock();

        $options = $roleOptionsProvider->getOptions([], $multiSelectMock);

        $this->assertCount(2, $options);

        $this->assertArrayHasKey('key', $options[0]);
        $this->assertArrayHasKey('value', $options[0]);
        $this->assertEquals('ROLE_USER', $options[0]['key']);
        $this->assertEquals('ROLE_USER', $options[0]['value']);

        $this->assertArrayHasKey('key', $options[1]);
        $this->assertArrayHasKey('value', $options[1]);
        $this->assertEquals('ROLE_MEMBERS_MODERATOR', $options[1]['key']);
        $this->assertEquals('ROLE_MEMBERS_MODERATOR', $options[1]['value']);

    }

}