<?php

namespace DachcomBundle\Test\unit\Manager;

use DachcomBundle\Test\Test\DachcomBundleTestCase;
use MembersBundle\Manager\ClassManager;
use Pimcore\Model\DataObject\MembersGroup;
use Pimcore\Model\DataObject\MembersUser;

class ClassManagerTest extends DachcomBundleTestCase
{
    /**
     * @throws \Codeception\Exception\ModuleException
     */
    public function testGroupClass()
    {
        $classManager = $this->getContainer()->get(ClassManager::class);
        $groupClass = $classManager->getGroupClass();

        $this->assertEquals(MembersGroup::class, $groupClass);
    }

    /**
     * @throws \Codeception\Exception\ModuleException
     */
    public function testGroupListing()
    {
        $classManager = $this->getContainer()->get(ClassManager::class);
        $groupListing = $classManager->getGroupListing();

        $this->assertInstanceOf(MembersGroup\Listing::class, $groupListing);
    }

    /**
     * @throws \Codeception\Exception\ModuleException
     */
    public function testUserClass()
    {
        $classManager = $this->getContainer()->get(ClassManager::class);
        $userClass = $classManager->getUserClass();

        $this->assertEquals(MembersUser::class, $userClass);
    }

    /**
     * @throws \Codeception\Exception\ModuleException
     */
    public function testUserListing()
    {
        $classManager = $this->getContainer()->get(ClassManager::class);
        $userListing = $classManager->getUserListing();

        $this->assertInstanceOf(MembersUser\Listing::class, $userListing);
    }
}
