<?php

namespace DachcomBundle\Test\functional\Validation;

use DachcomBundle\Test\FunctionalTester;
use DachcomBundle\Test\Util\MembersHelper;

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

        $I->see('members.validation.email.already_used',  '.form-error-message');
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

        $I->see('members.validation.username.already_used',  '.form-error-message');
    }

}