<?php

namespace DachcomBundle\Test\Functional\Validation;

use DachcomBundle\Test\Support\FunctionalTester;
use DachcomBundle\Test\Support\Util\MembersHelper;

class GetterCest
{
    public function testEmptyUserName(FunctionalTester $I)
    {
        $I->amOnPage('/en/members/register');
        $I->fillField('form[name="members_user_registration_form"] input[type="email"][id="members_user_registration_form_email"]', MembersHelper::DEFAULT_FEU_EMAIL);
        $I->fillField('form[name="members_user_registration_form"] input[type="text"][id="members_user_registration_form_username"]', '');
        $I->fillField('form[name="members_user_registration_form"] input[type="password"][id="members_user_registration_form_plainPassword_first"]', MembersHelper::DEFAULT_FEU_PASSWORD);
        $I->fillField('form[name="members_user_registration_form"] input[type="password"][id="members_user_registration_form_plainPassword_second"]', MembersHelper::DEFAULT_FEU_PASSWORD);
        $I->click('Register');

        $I->see('Please enter a username.',  '.form-error-message');
    }

    public function testShortUserName(FunctionalTester $I)
    {
        $I->amOnPage('/en/members/register');
        $I->fillField('form[name="members_user_registration_form"] input[type="email"][id="members_user_registration_form_email"]', MembersHelper::DEFAULT_FEU_EMAIL);
        $I->fillField('form[name="members_user_registration_form"] input[type="text"][id="members_user_registration_form_username"]', 'ch');
        $I->fillField('form[name="members_user_registration_form"] input[type="password"][id="members_user_registration_form_plainPassword_first"]', MembersHelper::DEFAULT_FEU_PASSWORD);
        $I->fillField('form[name="members_user_registration_form"] input[type="password"][id="members_user_registration_form_plainPassword_second"]', MembersHelper::DEFAULT_FEU_PASSWORD);
        $I->click('Register');

        $I->see('The username is too short.',  '.form-error-message');
    }

    public function testEmptyEmailAddress(FunctionalTester $I)
    {
        $I->amOnPage('/en/members/register');
        $I->fillField('form[name="members_user_registration_form"] input[type="email"][id="members_user_registration_form_email"]', '');
        $I->fillField('form[name="members_user_registration_form"] input[type="text"][id="members_user_registration_form_username"]', MembersHelper::DEFAULT_FEU_USERNAME);
        $I->fillField('form[name="members_user_registration_form"] input[type="password"][id="members_user_registration_form_plainPassword_first"]', MembersHelper::DEFAULT_FEU_PASSWORD);
        $I->fillField('form[name="members_user_registration_form"] input[type="password"][id="members_user_registration_form_plainPassword_second"]', MembersHelper::DEFAULT_FEU_PASSWORD);
        $I->click('Register');

        $I->see('Please enter an email.',  '.form-error-message');
    }

    public function testShortEmailAddress(FunctionalTester $I)
    {
        $I->amOnPage('/en/members/register');
        $I->fillField('form[name="members_user_registration_form"] input[type="email"][id="members_user_registration_form_email"]', 'a@');
        $I->fillField('form[name="members_user_registration_form"] input[type="text"][id="members_user_registration_form_username"]', MembersHelper::DEFAULT_FEU_USERNAME);
        $I->fillField('form[name="members_user_registration_form"] input[type="password"][id="members_user_registration_form_plainPassword_first"]', MembersHelper::DEFAULT_FEU_PASSWORD);
        $I->fillField('form[name="members_user_registration_form"] input[type="password"][id="members_user_registration_form_plainPassword_second"]', MembersHelper::DEFAULT_FEU_PASSWORD);
        $I->click('Register');

        $I->see('The email is too short.',  '.form-error-message');
    }

    public function testInvalidEmailAddress(FunctionalTester $I)
    {
        $I->amOnPage('/en/members/register');
        $I->fillField('form[name="members_user_registration_form"] input[type="email"][id="members_user_registration_form_email"]', 'invalid-email-address');
        $I->fillField('form[name="members_user_registration_form"] input[type="text"][id="members_user_registration_form_username"]', MembersHelper::DEFAULT_FEU_USERNAME);
        $I->fillField('form[name="members_user_registration_form"] input[type="password"][id="members_user_registration_form_plainPassword_first"]', MembersHelper::DEFAULT_FEU_PASSWORD);
        $I->fillField('form[name="members_user_registration_form"] input[type="password"][id="members_user_registration_form_plainPassword_second"]', MembersHelper::DEFAULT_FEU_PASSWORD);
        $I->click('Register');

        $I->see('The email is not valid.',  '.form-error-message');
    }

    public function testEmptyPassword(FunctionalTester $I)
    {
        $I->amOnPage('/en/members/register');
        $I->fillField('form[name="members_user_registration_form"] input[type="email"][id="members_user_registration_form_email"]', MembersHelper::DEFAULT_FEU_EMAIL);
        $I->fillField('form[name="members_user_registration_form"] input[type="text"][id="members_user_registration_form_username"]', MembersHelper::DEFAULT_FEU_USERNAME);
        $I->fillField('form[name="members_user_registration_form"] input[type="password"][id="members_user_registration_form_plainPassword_first"]', '');
        $I->fillField('form[name="members_user_registration_form"] input[type="password"][id="members_user_registration_form_plainPassword_second"]', '');
        $I->click('Register');

        $I->see('Please enter a password.',  '.form-error-message');
    }

    public function testShortPassword(FunctionalTester $I)
    {
        $I->amOnPage('/en/members/register');
        $I->fillField('form[name="members_user_registration_form"] input[type="email"][id="members_user_registration_form_email"]', MembersHelper::DEFAULT_FEU_EMAIL);
        $I->fillField('form[name="members_user_registration_form"] input[type="text"][id="members_user_registration_form_username"]', MembersHelper::DEFAULT_FEU_USERNAME);
        $I->fillField('form[name="members_user_registration_form"] input[type="password"][id="members_user_registration_form_plainPassword_first"]', 'pa');
        $I->fillField('form[name="members_user_registration_form"] input[type="password"][id="members_user_registration_form_plainPassword_second"]', 'pa');
        $I->click('Register');

        $I->see('The password is too short.',  '.form-error-message');
    }
}