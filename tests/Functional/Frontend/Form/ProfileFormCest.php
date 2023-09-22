<?php

namespace DachcomBundle\Test\functional\Frontend\Form;

use DachcomBundle\Test\Support\FunctionalTester;
use DachcomBundle\Test\Util\MembersHelper;

class ProfileFormCest
{
    /**
     * @param FunctionalTester $I
     */
    public function testProfileOverview(FunctionalTester $I)
    {
        $user = $I->haveARegisteredFrontEndUser(true);
        $I->amLoggedInAsFrontendUser($user, 'members_fe');

        $I->amOnPage('/en/members/profile');
        $I->see(sprintf('Username: %s', MembersHelper::DEFAULT_FEU_USERNAME), '.members_user_show');
        $I->see(sprintf('Email: %s', MembersHelper::DEFAULT_FEU_EMAIL), '.members_user_show');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testProfileEditForm(FunctionalTester $I)
    {
        $user = $I->haveARegisteredFrontEndUser(true);
        $I->amLoggedInAsFrontendUser($user, 'members_fe');

        $I->amOnPage('/en/members/profile/edit');
        $I->see('Username', 'form[name="members_user_profile_form"] label');
        $I->seeElement('form[name="members_user_profile_form"] input[type="text"][id="members_user_profile_form_username"]');
        $I->see('Email', 'form[name="members_user_profile_form"] label');
        $I->seeElement('form[name="members_user_profile_form"] input[type="email"][id="members_user_profile_form_email"]');
        $I->see('Current password', 'form[name="members_user_profile_form"] label');
        $I->seeElement('form[name="members_user_profile_form"] input[type="password"][id="members_user_profile_form_current_password"]');
        $I->seeElement('form[name="members_user_profile_form"] button[type="submit"][id="members_user_profile_form_submit"]');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testProfileEditFormUpdateInvalid(FunctionalTester $I)
    {
        $user = $I->haveARegisteredFrontEndUser(true);
        $I->amLoggedInAsFrontendUser($user, 'members_fe');

        $I->amOnPage('/en/members/profile/edit');

        $I->fillField('form[name="members_user_profile_form"] input[type="text"][id="members_user_profile_form_username"]', 'new-chuck');
        $I->fillField('form[name="members_user_profile_form"] input[type="email"][id="members_user_profile_form_email"]', 'new-test@universe.org');
        $I->fillField('form[name="members_user_profile_form"] input[type="password"][id="members_user_profile_form_current_password"]', 'wrong-password');
        $I->click('Update');

        $I->see('The entered password is invalid.', '.form-error-message');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testProfileEditFormUpdateValid(FunctionalTester $I)
    {
        $newUserName = 'new-' . MembersHelper::DEFAULT_FEU_USERNAME;
        $newEmail = 'new-' . MembersHelper::DEFAULT_FEU_EMAIL;

        $user = $I->haveARegisteredFrontEndUser(true);
        $I->amLoggedInAsFrontendUser($user, 'members_fe');

        $I->amOnPage('/en/members/profile/edit');

        $I->fillField('form[name="members_user_profile_form"] input[type="text"][id="members_user_profile_form_username"]', $newUserName);
        $I->fillField('form[name="members_user_profile_form"] input[type="email"][id="members_user_profile_form_email"]', $newEmail);
        $I->fillField('form[name="members_user_profile_form"] input[type="password"][id="members_user_profile_form_current_password"]', MembersHelper::DEFAULT_FEU_PASSWORD);
        $I->click('Update');

        $I->see('The profile has been updated.', '.alert.flash-success');
        $I->see(sprintf('Username: %s', $newUserName), '.members_user_show');
        $I->see(sprintf('Email: %s', $newEmail), '.members_user_show');
    }
}