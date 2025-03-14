<?php

namespace DachcomBundle\Test\Unit\Security;

use DachcomBundle\Test\Support\Test\DachcomBundleTestCase;
use MembersBundle\Security\UserProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Pimcore\Model\DataObject\MembersUser;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Manager\UserManagerInterface;
use DachcomBundle\Test\Support\Test\TestUser;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class UserProviderTest extends DachcomBundleTestCase
{
    private MockObject $userManager;
    private UserProvider $userProvider;

    protected function setUp(): void
    {
        $this->userManager = $this->getMockBuilder(UserManagerInterface::class)->getMock();
        $this->userProvider = new UserProvider('username', $this->userManager);
    }

    public function testLoadUserByIdentifier(): void
    {
        $user = $this->getMockBuilder(UserInterface::class)->getMock();

        $this->userManager->expects($this->once())
            ->method('findUserByUsername')
            ->with('foobar')
            ->willReturn($user);

        $this->assertSame($user, $this->userProvider->loadUserByIdentifier('foobar'));
    }

    public function testLoadUserByInvalidUsername(): void
    {
        $this->userManager->expects($this->once())
            ->method('findUserByUsername')
            ->with('foobar')
            ->willReturn(null);

        $this->expectException(UserNotFoundException::class);
        $this->userProvider->loadUserByIdentifier('foobar');
    }

    public function testRefreshUserBy(): void
    {
        $user = $this->getMockBuilder(MembersUser::class)
            ->onlyMethods(['getId'])
            ->getMock();

        $user->expects($this->once())
            ->method('getId')
            ->willReturn(2);

        $refreshedUser = $this->getMockBuilder(UserInterface::class)->getMock();

        $this->userManager->expects($this->once())
            ->method('findUserByCondition')
            ->with('oo_id = ?', [2])
            ->willReturn($refreshedUser);

        $this->userManager->expects($this->atLeastOnce())
            ->method('getClass')
            ->willReturn(get_class($user));

        $this->assertSame($refreshedUser, $this->userProvider->refreshUser($user));
    }

    public function testRefreshDeleted(): void
    {
        $user = $this->getMockBuilder(MembersUser::class)
            ->getMock();

        $this->userManager->expects($this->once())
            ->method('findUserByCondition')
            ->willReturn(null);
        $this->userManager->expects($this->atLeastOnce())
            ->method('getClass')
            ->willReturn(get_class($user));

        $this->expectException(UserNotFoundException::class);

        $this->userProvider->refreshUser($user);
    }

    public function testRefreshInvalidUser(): void
    {
        $user = $this->getMockBuilder(\Symfony\Component\Security\Core\User\UserInterface::class)->getMock();
        $this->userManager->expects($this->any())
            ->method('getClass')
            ->willReturn(get_class($user));

        $this->expectException(UnsupportedUserException::class);
        $this->userProvider->refreshUser($user);
    }

    public function testRefreshInvalidUserClass(): void
    {
        $user = $this->getMockBuilder(MembersUser::class)->getMock();
        $providedUser = $this->getMockBuilder(TestUser::class)->getMock();

        $this->userManager->expects($this->atLeastOnce())
            ->method('getClass')
            ->willReturn(get_class($user));

        $this->expectException(UnsupportedUserException::class);
        $this->userProvider->refreshUser($providedUser);
    }
}
