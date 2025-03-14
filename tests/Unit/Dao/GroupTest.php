<?php

namespace DachcomBundle\Test\Unit\Dao;

use DachcomBundle\Test\Support\Test\DachcomBundleTestCase;
use DachcomBundle\Test\Support\Util\MembersHelper;
use Pimcore\Model\DataObject\MembersGroup;

class GroupTest extends DachcomBundleTestCase
{
    /**
     * @throws \Exception
     */
    public function testGroupDaoEntity(): void
    {
        $group = $this->createUserGroup('group-1', ['ROLE_MEMBERS_MODERATOR']);
        $storedGroup = MembersGroup::getById($group->getId(), ['force' => true]);

        $this->assertInstanceOf(MembersGroup::class, $group);
        $this->assertEquals(MembersHelper::DEFAULT_FEG_NAME, $storedGroup->getName());
        $this->assertCount(1, $storedGroup->getRoles());
        $this->assertEquals('ROLE_MEMBERS_MODERATOR', $storedGroup->getRoles()[0]);
    }
}
