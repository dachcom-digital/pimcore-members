<?php

namespace DachcomBundle\Test\unit\Security;

use MembersBundle\Security\UserProvider;
use Codeception\TestCase\Test;
use Pimcore\Model\DataObject\MembersUser;

class UserProviderTest extends Test
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $userManager;
    /**
     * @var UserProvider
     */
    private $userProvider;

    protected function setUp()
    {
        $this->userManager = $this->getMockBuilder('MembersBundle\Manager\UserManagerInterface')->getMock();
        $this->userProvider = new UserProvider($this->userManager);
    }

    public function testLoadUserByUsername()
    {
        $user = $this->getMockBuilder('MembersBundle\Adapter\User\UserInterface')->getMock();

        $this->userManager->expects($this->once())
            ->method('findUserByUsername')
            ->with('foobar')
            ->will($this->returnValue($user));

        $this->assertSame($user, $this->userProvider->loadUserByUsername('foobar'));
    }

    public function testLoadUserByInvalidUsername()
    {
        $this->userManager->expects($this->once())
            ->method('findUserByUsername')
            ->with('foobar')
            ->will($this->returnValue(null));

        $this->expectException(\Symfony\Component\Security\Core\Exception\UsernameNotFoundException::class);
        $this->userProvider->loadUserByUsername('foobar');
    }

    public function testRefreshUserBy()
    {
        $user = $this->getMockBuilder(MembersUser::class)
            ->setMethods(['getId'])
            ->getMock();

        $user->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('2'));

        $refreshedUser = $this->getMockBuilder('MembersBundle\Adapter\User\UserInterface')->getMock();

        $this->userManager->expects($this->once())
            ->method('findUserByCondition')
            ->with('oo_id = ?', ['2'])
            ->will($this->returnValue($refreshedUser));

        $this->userManager->expects($this->atLeastOnce())
            ->method('getClass')
            ->will($this->returnValue(get_class($user)));

        $this->assertSame($refreshedUser, $this->userProvider->refreshUser($user));
    }

    public function testRefreshDeleted()
    {
        $user = $this->getMockForAbstractClass(MembersUser::class);

        $this->userManager->expects($this->once())
            ->method('findUserByCondition')
            ->will($this->returnValue(null));
        $this->userManager->expects($this->atLeastOnce())
            ->method('getClass')
            ->will($this->returnValue(get_class($user)));

        $this->expectException(\Symfony\Component\Security\Core\Exception\UsernameNotFoundException::class);
        $this->userProvider->refreshUser($user);
    }

    public function testRefreshInvalidUser()
    {
        $user = $this->getMockBuilder('Symfony\Component\Security\Core\User\UserInterface')->getMock();
        $this->userManager->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue(get_class($user)));

        $this->expectException(\Symfony\Component\Security\Core\Exception\UnsupportedUserException::class);
        $this->userProvider->refreshUser($user);
    }

    public function testRefreshInvalidUserClass()
    {
        $user = $this->getMockBuilder(MembersUser::class)->getMock();
        $providedUser = $this->getMockBuilder('DachcomBundle\Test\Test\TestUser')->getMock();

        $this->userManager->expects($this->atLeastOnce())
            ->method('getClass')
            ->will($this->returnValue(get_class($user)));

        $this->expectException(\Symfony\Component\Security\Core\Exception\UnsupportedUserException::class);
        $this->userProvider->refreshUser($providedUser);
    }
}