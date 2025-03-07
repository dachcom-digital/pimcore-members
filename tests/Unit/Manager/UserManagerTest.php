<?php

namespace DachcomBundle\Test\Unit\Manager;

use DachcomBundle\Test\Support\Test\DachcomBundleTestCase;
use DachcomBundle\Test\Support\Util\MembersHelper;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Manager\UserManager;
use Pimcore\Model\DataObject\MembersUser;

class UserManagerTest extends DachcomBundleTestCase
{
    public function testClassGetter(): void
    {
        $userManager = $this->getContainer()->get(UserManager::class);
        $groupClass = $userManager->getClass();

        $this->assertEquals(MembersUser::class, $groupClass);
    }

    public function testCreateNewUser(): void
    {
        $user = $this->createUser();
        $this->assertInstanceOf(UserInterface::class, $user);

        $expectedKey = \Pimcore\File::getValidFilename($user->getEmail());
        $this->assertEquals($expectedKey, $user->getKey());
    }

    public function testFindUserByEmail(): void
    {
        $this->createUser();
        $userManager = $this->getContainer()->get(UserManager::class);

        $this->assertInstanceOf(UserInterface::class, $userManager->findUserByEmail(MembersHelper::DEFAULT_FEU_EMAIL));
    }

    public function testFindUserByUsername(): void
    {
        $this->createUser();
        $userManager = $this->getContainer()->get(UserManager::class);

        $this->assertInstanceOf(UserInterface::class, $userManager->findUserByUsername(MembersHelper::DEFAULT_FEU_USERNAME));
    }

    public function testFindUserByUsernameOrEmail(): void
    {
        $this->createUser();
        $userManager = $this->getContainer()->get(UserManager::class);

        $this->assertInstanceOf(UserInterface::class, $userManager->findUserByUsernameOrEmail(MembersHelper::DEFAULT_FEU_EMAIL));
        $this->assertInstanceOf(UserInterface::class, $userManager->findUserByUsernameOrEmail(MembersHelper::DEFAULT_FEU_USERNAME));
    }

    public function testFindUserByCondition(): void
    {
        $this->createUser();
        $userManager = $this->getContainer()->get(UserManager::class);

        $this->assertInstanceOf(UserInterface::class, $userManager->findUserByCondition('email = ?', [MembersHelper::DEFAULT_FEU_EMAIL]));
    }

    public function testFindPublishedUsers(): void
    {
        $this->createUser(true);
        $userManager = $this->getContainer()->get(UserManager::class);

        $this->assertCount(1, $userManager->findUsers());
    }

    public function testFindUnPublishedUsers(): void
    {
        $user = $this->createUser(false);
        $userManager = $this->getContainer()->get(UserManager::class);

        $this->assertCount(0, $userManager->findUsers());
    }
}
