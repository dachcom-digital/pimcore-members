<?php

namespace DachcomBundle\Test\functional\Constraints;

use DachcomBundle\Test\FunctionalTester;

class LoginLogoutCest
{
    /**
     * @param FunctionalTester $I
     */
    public function testUserLoginForm(FunctionalTester $I)
    {
        $I->amOnPage('/en/members/login');
        $I->see('Username', 'form[class="members_user_login"] label');
        $I->seeElement('form[class="members_user_login"] input[type="text"][name="_username"]');
        $I->see('Password', 'form[class="members_user_login"] label');
        $I->seeElement('form[class="members_user_login"] input[type="password"][name="_password"]');
        $I->see('Remember me', 'form[class="members_user_login"] label');
        $I->seeElement('form[class="members_user_login"] input[type="checkbox"][name="_remember_me"]');
        $I->seeElement('form[class="members_user_login"] button[type="submit"][name="_submit"]');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testLoginWithNonExistingUser(FunctionalTester $I)
    {
        $this->login($I);
        $I->see('invalid credentials.', 'div');
        $I->haveANotLoggedInFrontEndUser();
    }

    /**
     * @param FunctionalTester $I
     */
    public function testLoginWithInactiveUser(FunctionalTester $I)
    {
        $I->haveARegisteredFrontEndUser(false);

        $this->login($I);
        $I->see('Account is disabled.', 'div');
        $I->haveANotLoggedInFrontEndUser();
    }

    /**
     * @param FunctionalTester $I
     */
    public function testLoginWithValidUser(FunctionalTester $I)
    {
        $I->haveARegisteredFrontEndUser(true);
        $this->login($I);
        $I->haveALoggedInFrontEndUser(true);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testLogout(FunctionalTester $I)
    {
        $I->haveARegisteredFrontEndUser(true);
        $this->login($I);
        $I->haveALoggedInFrontEndUser(true);

        $I->amOnPage('/en/members/logout');
        $I->haveANotLoggedInFrontEndUser(true);
    }

    /**
     * @param FunctionalTester $I
     */
    private function login(FunctionalTester $I)
    {
        $userName = 'chuck';
        $password = 'test';

        $I->amOnPage('/en/members/login');
        $I->fillField('form[class="members_user_login"] input[type="text"][name="_username"]', $userName);
        $I->fillField('form[class="members_user_login"] input[type="password"][name="_password"]', $password);
        $I->click('Log In');
    }
}