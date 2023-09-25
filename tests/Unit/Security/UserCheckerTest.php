<?php

namespace DachcomBundle\Test\Unit\Security;

use DachcomBundle\Test\Support\Test\DachcomBundleTestCase;
use MembersBundle\Security\UserChecker;
use Pimcore\Model\DataObject\MembersUser;

class UserCheckerTest extends DachcomBundleTestCase
{
    public function testCheckPreAuthFailsLockedOut()
    {
        $this->expectException(\Symfony\Component\Security\Core\Exception\LockedException::class);
        $this->expectExceptionMessage('User account is locked.');

        $userMock = $this->getUser(false, false, false);
        $checker = new UserChecker();
        $checker->checkPreAuth($userMock);
    }

    public function testCheckPreAuthFailsIsPublished()
    {
        $this->expectException(\Symfony\Component\Security\Core\Exception\DisabledException::class);
        $this->expectExceptionMessage('User account is disabled.');

        $userMock = $this->getUser(true, false, false);
        $checker = new UserChecker();
        $checker->checkPreAuth($userMock);
    }

    public function testCheckPreAuthFailsIsAccountNonExpired()
    {
        $this->expectException(\Symfony\Component\Security\Core\Exception\AccountExpiredException::class);
        $this->expectExceptionMessage('User account has expired.');

        $userMock = $this->getUser(true, true, false);
        $checker = new UserChecker();
        $checker->checkPreAuth($userMock);
    }

    public function testCheckPreAuthSuccess()
    {
        $userMock = $this->getUser(true, true, true);
        $checker = new UserChecker();

        $this->assertNull($checker->checkPreAuth($userMock));
    }

    public function testCheckPostAuthSuccess()
    {
        $userMock = $this->getUser(true, true, true);
        $checker = new UserChecker();

        $this->assertNull($checker->checkPostAuth($userMock));
    }

    private function getUser($isAccountNonLocked, $isPublished, $isAccountNonExpired)
    {
        $userMock = $this->getMockBuilder(MembersUser::class)->getMock();
        $userMock
            ->method('isAccountNonLocked')
            ->willReturn($isAccountNonLocked);
        $userMock
            ->method('getPublished')
            ->willReturn($isPublished);
        $userMock
            ->method('isAccountNonExpired')
            ->willReturn($isAccountNonExpired);

        return $userMock;
    }
}
