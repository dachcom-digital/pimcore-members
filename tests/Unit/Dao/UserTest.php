<?php

namespace DachcomBundle\Test\Unit\Dao;

use DachcomBundle\Test\Support\Test\DachcomBundleTestCase;
use DachcomBundle\Test\Support\Util\MembersHelper;
use Pimcore\Model\DataObject\MembersGroup;
use Pimcore\Model\DataObject\MembersUser;

class UserTest extends DachcomBundleTestCase
{
    /**
     * @throws \Codeception\Exception\ModuleException
     * @throws \Exception
     */
    public function testUserDaoEntity()
    {
        $group1 = $this->createUserGroup('group-1');
        $group2 = $this->createUserGroup('group-2');

        $user = $this->createUser(true, [$group1, $group2]);
        $storedUser = MembersUser::getById($user->getId(), ['force' => true]);

        $this->assertInstanceOf(MembersUser::class, $user);
        $this->assertEquals(MembersHelper::DEFAULT_FEU_EMAIL, $storedUser->getEmail());
        $this->assertEquals(MembersHelper::DEFAULT_FEU_USERNAME, $storedUser->getUserName());
        $this->assertEquals(null, $storedUser->getPlainPassword());

        $this->assertCount(2, $storedUser->getGroups());
        $this->assertInstanceOf(MembersGroup::class, $storedUser->getGroups()[0]);
        $this->assertInstanceOf(MembersGroup::class, $storedUser->getGroups()[1]);
        $this->assertEquals([$group1->getName(), $group2->getName()], $storedUser->getGroupNames());
    }
}
