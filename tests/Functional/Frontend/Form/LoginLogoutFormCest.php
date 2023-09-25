<?php

namespace DachcomBundle\Test\Functional\Frontend\Form;

use DachcomBundle\Test\Support\FunctionalTester;
use DachcomBundle\Test\Support\Util\MembersHelper;

class LoginLogoutFormCest
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
        $I->see('Username could not be found.', 'div');
        $I->seeANotLoggedInFrontEndUser();
    }

    /**
     * @param FunctionalTester $I
     */
    public function testLoginWithInactiveUser(FunctionalTester $I)
    {
        $I->haveARegisteredFrontEndUser(false);
        $this->login($I);
        $I->see('Account is disabled.', 'div');
        $I->seeANotLoggedInFrontEndUser();
    }

    /**
     * @param FunctionalTester $I
     */
    public function testLoginWithValidUser(FunctionalTester $I)
    {
        $I->haveARegisteredFrontEndUser(true);
        $this->login($I);
        $I->seeALoggedInFrontEndUser();
    }

    /**
     * @param FunctionalTester $I
     */
    public function testLogout(FunctionalTester $I)
    {
        $I->haveARegisteredFrontEndUser(true);
        $this->login($I);
        $I->seeALoggedInFrontEndUser(true);
        $I->amOnPage('/en/members/logout');
        $I->seeANotLoggedInFrontEndUser();
    }

    /**
     * @param FunctionalTester $I
     */
    private function login(FunctionalTester $I)
    {
        $I->amOnPage('/en/members/login');
        $I->fillField('form[class="members_user_login"] input[type="text"][name="_username"]', MembersHelper::DEFAULT_FEU_USERNAME);
        $I->fillField('form[class="members_user_login"] input[type="password"][name="_password"]', MembersHelper::DEFAULT_FEU_PASSWORD);
        $I->click('Log In');
    }
}