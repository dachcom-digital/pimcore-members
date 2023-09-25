<?php

namespace DachcomBundle\Test\Functional\Validation;

use DachcomBundle\Test\Support\FunctionalTester;
use DachcomBundle\Test\Support\Util\MembersHelper;

class UniqueCest
{
    public function testUniqueEmailAddress(FunctionalTester $I)
    {
        $I->haveARegisteredFrontEndUser();

        $I->amOnPage('/en/members/register');
        $I->fillField('form[name="members_user_registration_form"] input[type="email"][id="members_user_registration_form_email"]', MembersHelper::DEFAULT_FEU_EMAIL);
        $I->fillField('form[name="members_user_registration_form"] input[type="text"][id="members_user_registration_form_username"]', 'another-username');
        $I->fillField('form[name="members_user_registration_form"] input[type="password"][id="members_user_registration_form_plainPassword_first"]', 'password');
        $I->fillField('form[name="members_user_registration_form"] input[type="password"][id="members_user_registration_form_plainPassword_second"]', 'password');
        $I->click('Register');

        $I->see('The email is already used.',  '.form-error-message');
    }

    public function testUniqueUsername(FunctionalTester $I)
    {
        $I->haveARegisteredFrontEndUser();

        $I->amOnPage('/en/members/register');
        $I->fillField('form[name="members_user_registration_form"] input[type="email"][id="members_user_registration_form_email"]', 'another-email@address.com');
        $I->fillField('form[name="members_user_registration_form"] input[type="text"][id="members_user_registration_form_username"]', MembersHelper::DEFAULT_FEU_USERNAME);
        $I->fillField('form[name="members_user_registration_form"] input[type="password"][id="members_user_registration_form_plainPassword_first"]', 'password');
        $I->fillField('form[name="members_user_registration_form"] input[type="password"][id="members_user_registration_form_plainPassword_second"]', 'password');
        $I->click('Register');

        $I->see('The username is already used.',  '.form-error-message');
    }

}