<?php

namespace DachcomBundle\Test\unit\Dao;

use DachcomBundle\Test\Test\DachcomBundleTestCase;
use DachcomBundle\Test\Util\MembersHelper;
use Pimcore\Model\DataObject\MembersGroup;

class GroupTest extends DachcomBundleTestCase
{
    /**
     * @throws \Exception
     */
    public function testGroupDaoEntity()
    {
        $group = $this->createUserGroup('group-1', ['ROLE_MEMBERS_MODERATOR']);
        $storedGroup = MembersGroup::getById($group->getId(), ['force' => true]);

        $this->assertInstanceOf(MembersGroup::class, $group);
        $this->assertEquals(MembersHelper::DEFAULT_FEG_NAME, $storedGroup->getName());
        $this->assertCount(1, $storedGroup->getRoles());
        $this->assertEquals('ROLE_MEMBERS_MODERATOR', $storedGroup->getRoles()[0]);
    }
}
