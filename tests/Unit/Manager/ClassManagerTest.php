<?php

namespace DachcomBundle\Test\Unit\Manager;

use Codeception\Exception\ModuleException;
use DachcomBundle\Test\Support\Test\DachcomBundleTestCase;
use MembersBundle\Manager\ClassManager;
use Pimcore\Model\DataObject\MembersGroup;
use Pimcore\Model\DataObject\MembersUser;

class ClassManagerTest extends DachcomBundleTestCase
{
    /**
     * @throws ModuleException
     */
    public function testGroupClass()
    {
        $classManager = $this->getContainer()->get(ClassManager::class);
        $groupClass = $classManager->getGroupClass();

        $this->assertEquals(MembersGroup::class, $groupClass);
    }

    /**
     * @throws ModuleException
     */
    public function testGroupListing()
    {
        $classManager = $this->getContainer()->get(ClassManager::class);
        $groupListing = $classManager->getGroupListing();

        $this->assertInstanceOf(MembersGroup\Listing::class, $groupListing);
    }

    /**
     * @throws ModuleException
     */
    public function testUserClass()
    {
        $classManager = $this->getContainer()->get(ClassManager::class);
        $userClass = $classManager->getUserClass();

        $this->assertEquals(MembersUser::class, $userClass);
    }

    /**
     * @throws ModuleException
     */
    public function testUserListing()
    {
        $classManager = $this->getContainer()->get(ClassManager::class);
        $userListing = $classManager->getUserListing();

        $this->assertInstanceOf(MembersUser\Listing::class, $userListing);
    }
}
