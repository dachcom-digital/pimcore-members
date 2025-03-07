<?php

namespace DachcomBundle\Test\Unit\Manager;

use Codeception\Exception\ModuleException;
use DachcomBundle\Test\Support\Test\DachcomBundleTestCase;
use MembersBundle\Manager\ClassManager;
use Pimcore\Model\DataObject\MembersGroup;
use Pimcore\Model\DataObject\MembersUser;

class ClassManagerTest extends DachcomBundleTestCase
{
    public function testGroupClass(): void
    {
        $classManager = $this->getContainer()->get(ClassManager::class);
        $groupClass = $classManager->getGroupClass();

        $this->assertEquals(MembersGroup::class, $groupClass);
    }

    public function testGroupListing(): void
    {
        $classManager = $this->getContainer()->get(ClassManager::class);
        $groupListing = $classManager->getGroupListing();

        $this->assertInstanceOf(MembersGroup\Listing::class, $groupListing);
    }

    public function testUserClass(): void
    {
        $classManager = $this->getContainer()->get(ClassManager::class);
        $userClass = $classManager->getUserClass();

        $this->assertEquals(MembersUser::class, $userClass);
    }

    public function testUserListing(): void
    {
        $classManager = $this->getContainer()->get(ClassManager::class);
        $userListing = $classManager->getUserListing();

        $this->assertInstanceOf(MembersUser\Listing::class, $userListing);
    }
}
