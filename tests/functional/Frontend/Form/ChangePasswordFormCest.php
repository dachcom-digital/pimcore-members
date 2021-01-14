<?php

namespace DachcomBundle\Test\functional\Frontend\Form;

use DachcomBundle\Test\FunctionalTester;
use DachcomBundle\Test\Util\MembersHelper;

class ChangePasswordFormCest
{
    /**
     * @param FunctionalTester $I
     */
    public function testChangePasswordForm(FunctionalTester $I)
    {
        $user = $I->haveARegisteredFrontEndUser(true);
        $I->amLoggedInAsFrontendUser($user, 'members_fe');

        $I->amOnPage('/en/members/profile/change-password');
        $I->see('Current password', 'form[name="members_user_change_password_form"] label');
        $I->seeElement('form[name="members_user_change_password_form"] input[type="password"][id="members_user_change_password_form_current_password"]');
        $I->see('New password', 'form[name="members_user_change_password_form"] label');
        $I->seeElement('form[name="members_user_change_password_form"] input[type="password"][id="members_user_change_password_form_plainPassword_first"]');
        $I->see('Repeat new password', 'form[name="members_user_change_password_form"] label');
        $I->seeElement('form[name="members_user_change_password_form"] input[type="password"][id="members_user_change_password_form_plainPassword_second"]');
        $I->seeElement('form[name="members_user_change_password_form"] button[type="submit"][id="members_user_change_password_form_submit"]');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testChangePassword(FunctionalTester $I)
    {
        $user = $I->haveARegisteredFrontEndUser(true);
        $I->amLoggedInAsFrontendUser($user, 'members_fe');

        $I->amOnPage('/en/members/profile/change-password');
        $I->fillField('form[name="members_user_change_password_form"] input[type="password"][id="members_user_change_password_form_current_password"]', MembersHelper::DEFAULT_FEU_PASSWORD);
        $I->fillField('form[name="members_user_change_password_form"] input[type="password"][id="members_user_change_password_form_plainPassword_first"]', 'default-new-password');
        $I->fillField('form[name="members_user_change_password_form"] input[type="password"][id="members_user_change_password_form_plainPassword_second"]', 'default-new-password');
        $I->click('Change password');

        $I->see('The password has been changed.', '.alert.flash-success');
        $I->see(sprintf('Username: %s', MembersHelper::DEFAULT_FEU_USERNAME), '.members_user_show');
        $I->see(sprintf('Email: %s', MembersHelper::DEFAULT_FEU_EMAIL), '.members_user_show');
    }
}