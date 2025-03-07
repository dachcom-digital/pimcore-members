<?php

namespace DachcomBundle\Test\Unit\Security;

use DachcomBundle\Test\Support\Test\DachcomBundleTestCase;
use MembersBundle\Security\UserChecker;
use Pimcore\Model\DataObject\MembersUser;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\LockedException;

class UserCheckerTest extends DachcomBundleTestCase
{
    public function testCheckPreAuthFailsLockedOut(): void
    {
        $this->expectException(LockedException::class);
        $this->expectExceptionMessage('User account is locked.');

        $userMock = $this->getUser(false, false, false);
        $checker = new UserChecker();
        $checker->checkPreAuth($userMock);
    }

    public function testCheckPreAuthFailsIsPublished(): void
    {
        $this->expectException(DisabledException::class);
        $this->expectExceptionMessage('User account is disabled.');

        $userMock = $this->getUser(true, false, false);
        $checker = new UserChecker();
        $checker->checkPreAuth($userMock);
    }

    public function testCheckPreAuthFailsIsAccountNonExpired(): void
    {
        $this->expectException(AccountExpiredException::class);
        $this->expectExceptionMessage('User account has expired.');

        $userMock = $this->getUser(true, true, false);
        $checker = new UserChecker();
        $checker->checkPreAuth($userMock);
    }

    public function testCheckPreAuthSuccess(): void
    {
        $userMock = $this->getUser(true, true, true);
        $checker = new UserChecker();

        $this->assertNull($checker->checkPreAuth($userMock));
    }

    public function testCheckPostAuthSuccess(): void
    {
        $userMock = $this->getUser(true, true, true);
        $checker = new UserChecker();

        $this->assertNull($checker->checkPostAuth($userMock));
    }

    private function getUser($isAccountNonLocked, $isPublished, $isAccountNonExpired): MembersUser
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
