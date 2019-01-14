<?php

namespace DachcomBundle\Test\functional\Frontend\Form;

use DachcomBundle\Test\FunctionalTester;
use DachcomBundle\Test\Util\MembersHelper;

class DeleteAccountFormCest
{
    /**
     * @param FunctionalTester $I
     */
    public function testDeleteAccountForm(FunctionalTester $I)
    {
        $user = $I->haveARegisteredFrontEndUser(true);
        $I->amLoggedInAsFrontendUser($user);

        $I->amOnPage('/en/members/profile/delete-account');
        $I->see('Current password', 'form[name="members_user_delete_account_form"] label');
        $I->seeElement('form[name="members_user_delete_account_form"] input[type="password"][id="members_user_delete_account_form_current_password_first"]');
        $I->see('Repeat password', 'form[name="members_user_delete_account_form"] label');
        $I->seeElement('form[name="members_user_delete_account_form"] input[type="password"][id="members_user_delete_account_form_current_password_second"]');
        $I->see('Yes, I want to delete my account', 'form[name="members_user_delete_account_form"] label');
        $I->seeElement('form[name="members_user_delete_account_form"] input[type="checkbox"][id="members_user_delete_account_form_deleteConfirm"]');
        $I->seeElement('form[name="members_user_delete_account_form"] button[type="submit"][id="members_user_delete_account_form_submit"]');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testDeleteAccountInvalid(FunctionalTester $I)
    {
        $user = $I->haveARegisteredFrontEndUser(true);
        $I->amLoggedInAsFrontendUser($user);

        $I->amOnPage('/en/members/profile/delete-account');
        $I->fillField('form[name="members_user_delete_account_form"] input[type="password"][id="members_user_delete_account_form_current_password_first"]',
            MembersHelper::DEFAULT_FEU_PASSWORD);
        $I->fillField('form[name="members_user_delete_account_form"] input[type="password"][id="members_user_delete_account_form_current_password_second"]',
            MembersHelper::DEFAULT_FEU_PASSWORD);
        $I->click('Delete account');

        $I->see('You have to confirm the account deletion.', '.form-error-message');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testDeleteAccountValid(FunctionalTester $I)
    {
        $user = $I->haveARegisteredFrontEndUser(true);
        $I->amLoggedInAsFrontendUser($user);

        $I->amOnPage('/en/members/profile/delete-account');
        $I->fillField('form[name="members_user_delete_account_form"] input[type="password"][id="members_user_delete_account_form_current_password_first"]',
            MembersHelper::DEFAULT_FEU_PASSWORD);
        $I->fillField('form[name="members_user_delete_account_form"] input[type="password"][id="members_user_delete_account_form_current_password_second"]',
            MembersHelper::DEFAULT_FEU_PASSWORD);
        $I->checkOption('input[id="members_user_delete_account_form_deleteConfirm"]');
        $I->click('Delete account');

        $I->seeANotLoggedInFrontEndUser();
        $I->seeNoFrontendUserInStorage();
    }
}