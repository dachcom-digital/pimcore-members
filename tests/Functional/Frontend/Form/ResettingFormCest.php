<?php

namespace DachcomBundle\Test\Functional\Frontend\Form;

use DachcomBundle\Test\Support\FunctionalTester;
use DachcomBundle\Test\Support\Util\MembersHelper;
use Pimcore\Model\Document\Email;

class ResettingFormCest
{
    public function testResettingForm(FunctionalTester $I): void
    {
        $I->amOnPage('/en/members/resetting/request');

        $I->see('Username or email address', 'form[class="members_user_resetting_request"] label');
        $I->seeElement('form[class="members_user_resetting_request"] input[type="text"][id="username"]');
        $I->seeElement('form[class="members_user_resetting_request"] button[type="submit"][id="submit"]');
    }

    public function testResettingByUsername(FunctionalTester $I): void
    {
        $user = $I->haveARegisteredFrontEndUser(true);
        $this->triggerResetForm($I, $user->getUserName());
    }

    public function testResettingByEmailAddress(FunctionalTester $I): void
    {
        $user = $I->haveARegisteredFrontEndUser(true);
        $this->triggerResetForm($I, $user->getEmail());
    }

    public function testResettingWithAdminConfirm(FunctionalTester $I): void
    {
        $I->haveABootedSymfonyConfiguration('config_reg_confirm_by_admin_with_after_confirmed.yaml');

        $user = $I->haveARegisteredFrontEndUser(true);
        $this->triggerResetForm($I, $user->getEmail());
    }

    private function triggerResetForm(FunctionalTester $I, $field): void
    {
        $I->amOnPage('/en/members/resetting/request');

        $I->fillField('form[class="members_user_resetting_request"] input[type="text"][id="username"]', $field);
        $I->click('Reset password');

        $confirmText = 'An email has been sent. It contains a link you must click to reset your password. ';
        $confirmText .= 'Note: You can only request a new password once within 2 hours. ';
        $confirmText .= 'If you don';

        $I->see(htmlspecialchars($confirmText), 'div p');

        $email = Email::getByPath('/email/password-reset');
        $I->canSeeEmailIsSent($email);
        $I->seePropertyKeysInEmail($email, ['user', 'confirmationUrl']);

        $confirmationLink = $I->haveConfirmationLinkInEmail($email);

        $I->amOnPage($confirmationLink);
        $I->see('New password', 'form[name="members_user_resetting_form"] label');
        $I->seeElement('form[name="members_user_resetting_form"] input[type="password"][id="members_user_resetting_form_plainPassword_first"]');
        $I->see('Repeat new password', 'form[name="members_user_resetting_form"] label');
        $I->seeElement('form[name="members_user_resetting_form"] input[type="password"][id="members_user_resetting_form_plainPassword_second"]');
        $I->seeElement('form[name="members_user_resetting_form"] button[type="submit"][id="members_user_resetting_form_submit"]');

        $I->fillField('form[name="members_user_resetting_form"] input[type="password"][id="members_user_resetting_form_plainPassword_first"]', 'new-pass');
        $I->fillField('form[name="members_user_resetting_form"] input[type="password"][id="members_user_resetting_form_plainPassword_second"]', 'new-pass');
        $I->click('Change password');

        $I->see('The password has been reset successfully.', '.alert.flash-success');
        $I->see(sprintf('Username: %s', MembersHelper::DEFAULT_FEU_USERNAME), '.members_user_show');
        $I->see(sprintf('Email: %s', MembersHelper::DEFAULT_FEU_EMAIL), '.members_user_show');
    }
}
