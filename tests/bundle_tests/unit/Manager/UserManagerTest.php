<?php

namespace DachcomBundle\Test\unit\Manager;

use DachcomBundle\Test\Test\DachcomBundleTestCase;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Manager\UserManager;
use Pimcore\Model\DataObject\MembersUser;

class UserManagerTest extends DachcomBundleTestCase
{
    /**
     * @throws \Codeception\Exception\ModuleException
     */
    public function testClassGetter()
    {
        $userManager = $this->getContainer()->get(UserManager::class);
        $groupClass = $userManager->getClass();

        $this->assertEquals(MembersUser::class, $groupClass);
    }

    public function testCreateNewUser()
    {
        $user = $this->createUser();
        $this->assertInstanceOf(UserInterface::class, $user);

        $expectedKey = \Pimcore\File::getValidFilename($user->getEmail());
        $this->assertEquals($expectedKey, $user->getKey());
    }

    public function testFindUserByEmail()
    {
        $this->createUser();
        $userManager = $this->getContainer()->get(UserManager::class);

        $this->assertInstanceOf(UserInterface::class, $userManager->findUserByEmail('test@universe.org'));
    }

    public function testFindUserByUsername()
    {
        $this->createUser();
        $userManager = $this->getContainer()->get(UserManager::class);

        $this->assertInstanceOf(UserInterface::class, $userManager->findUserByUsername('chuck'));
    }

    public function testFindUserByUsernameOrEmail()
    {
        $this->createUser();
        $userManager = $this->getContainer()->get(UserManager::class);

        $this->assertInstanceOf(UserInterface::class, $userManager->findUserByUsernameOrEmail('test@universe.org'));
        $this->assertInstanceOf(UserInterface::class, $userManager->findUserByUsernameOrEmail('chuck'));
    }

    public function testFindUserByCondition()
    {
        $this->createUser();
        $userManager = $this->getContainer()->get(UserManager::class);

        $this->assertInstanceOf(UserInterface::class, $userManager->findUserByCondition('email = ?', ['test@universe.org']));
    }

    public function testFindPublishedUsers()
    {
        $this->createUser(true);
        $userManager = $this->getContainer()->get(UserManager::class);

        $this->assertCount(1, $userManager->findUsers());
    }

    public function testFindUnPublishedUsers()
    {
        $this->createUser(false);
        $userManager = $this->getContainer()->get(UserManager::class);

        $this->assertCount(0, $userManager->findUsers());
    }
}
