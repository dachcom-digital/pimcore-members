<?php

namespace DachcomBundle\Test\unit\Security;

use MembersBundle\Security\EmailUserProvider;
use PHPUnit\Framework\TestCase;
use Pimcore\Model\DataObject\MembersUser;

class EmailUserProviderTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $userManager;
    /**
     * @var EmailUserProvider
     */
    private $userProvider;

    protected function setUp()
    {
        $this->userManager = $this->getMockBuilder('MembersBundle\Manager\UserManagerInterface')->getMock();
        $this->userProvider = new EmailUserProvider($this->userManager);
    }

    public function testLoadUserByUsername()
    {
        $user = $this->getMockBuilder('MembersBundle\Adapter\User\UserInterface')->getMock();
        $this->userManager->expects($this->once())
            ->method('findUserByUsernameOrEmail')
            ->with('foobar')
            ->will($this->returnValue($user));
        $this->assertSame($user, $this->userProvider->loadUserByUsername('foobar'));
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\UserNotFoundException
     */
    public function testLoadUserByInvalidUsername()
    {
        $this->userManager->expects($this->once())
            ->method('findUserByUsernameOrEmail')
            ->with('foobar')
            ->will($this->returnValue(null));
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

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\UnsupportedUserException
     */
    public function testRefreshInvalidUser()
    {
        $user = $this->getMockBuilder('Symfony\Component\Security\Core\User\UserInterface')->getMock();
        $this->userProvider->refreshUser($user);
    }
}