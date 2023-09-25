<?php

namespace DachcomBundle\Test\Functional\Frontend\Form;

use DachcomBundle\Test\Support\FunctionalTester;
use DachcomBundle\Test\Support\Util\MembersHelper;
use Pimcore\Model\Document\Email;

class RegisterFormCest
{
    /**
     * @param FunctionalTester $I
     */
    public function testUserRegistrationForm(FunctionalTester $I)
    {
        $I->amOnPage('/en/members/register');
        $I->see('Email', 'form[name="members_user_registration_form"] label');
        $I->seeElement('form[name="members_user_registration_form"] input[type="email"][id="members_user_registration_form_email"]');
        $I->see('Username', 'form[name="members_user_registration_form"] label');
        $I->seeElement('form[name="members_user_registration_form"] input[type="text"][id="members_user_registration_form_username"]');
        $I->see('Password', 'form[name="members_user_registration_form"] label');
        $I->seeElement('form[name="members_user_registration_form"] input[type="password"][id="members_user_registration_form_plainPassword_first"]');
        $I->see('Repeat password', 'form[name="members_user_registration_form"] label');
        $I->seeElement('form[name="members_user_registration_form"] input[type="password"][id="members_user_registration_form_plainPassword_second"]');
        $I->seeElement('form[name="members_user_registration_form"] button[type="submit"][id="members_user_registration_form_submit"]');
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testUserRegistrationFormConfirmByMail(FunctionalTester $I)
    {
        $this->register($I);

        $I->see('The user has been created successfully.', '.alert.flash-success');
        $I->see(sprintf('An email has been sent to %s. It contains an activation link you must click to activate your account.', MembersHelper::DEFAULT_FEU_EMAIL), 'p');

        $I->seeAUnpublishedUserAfterRegistration();
        $I->seeAUserWithValidToken();

        $email = Email::getByPath('/email/register-confirm');
        $I->canSeeEmailIsSent($email);
        $I->seePropertyKeysInEmail($email, ['user', 'confirmationUrl']);

        $confirmationLink = $I->haveConfirmationLinkInEmail($email);
        $I->amOnPage($confirmationLink);
        $I->see(sprintf('Congrats %s, your account is now activated.', MembersHelper::DEFAULT_FEU_USERNAME), 'p');

        $I->seeAPublishedUserAfterRegistration();
        $I->seeAUserWithInvalidatedToken();

        $email = Email::getByPath('/email/register-confirmed');
        $I->seeEmailIsNotSent($email);
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testUserRegistrationFormConfirmByAdmin(FunctionalTester $I)
    {
        $I->haveABootedSymfonyConfiguration('config_reg_confirm_by_admin.yaml');

        $this->register($I);

        $I->see('The user has been created successfully.', '.alert.flash-success');
        $I->see('Your account was created successfully and must be activated by site stuff.', 'p');

        $I->seeAUnpublishedUserAfterRegistration();
        $I->seeAUserWithValidToken();

        $email = Email::getByPath('/email/register-confirm');
        $I->seeEmailIsNotSent($email);

        $user = $I->grabOneUserAfterRegistration();
        $I->publishAndConfirmAFrontendUser($user);

        $email = Email::getByPath('/email/register-confirmed');
        $I->seeEmailIsNotSent($email);
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testUserRegistrationFormConfirmByAdminWithFinalConfirmationMail(FunctionalTester $I)
    {
        $I->haveABootedSymfonyConfiguration('config_reg_confirm_by_admin_with_after_confirmed.yaml');

        $this->register($I);

        $user = $I->grabOneUserAfterRegistration();
        $I->publishAndConfirmAFrontendUser($user);

        $email = Email::getByPath('/email/register-confirmed');
        $I->canSeeEmailIsSent($email);
        $I->seePropertyKeysInEmail($email, ['user', 'loginpage']);
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testUserRegistrationFormConfirmByAdminWithAdminNotificationMail(FunctionalTester $I)
    {
        $I->haveABootedSymfonyConfiguration('config_reg_confirm_by_admin_with_admin_notify.yaml');

        $email = Email::getByPath('/email/admin-register-notification');
        $email->setTo('test-admin@universe.org');
        $email->save();

        $this->register($I);

        $I->canSeeEmailIsSent($email);
        $I->seePropertyKeysInEmail($email, ['user', 'deeplink']);
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testUserRegistrationFormConfirmInstant(FunctionalTester $I)
    {
        $I->haveABootedSymfonyConfiguration('config_reg_confirm_instant.yaml');

        $this->register($I);

        $I->see('The user has been created successfully.', '.alert.flash-success');
        $I->see(sprintf('Congrats %s, your account is now activated.', MembersHelper::DEFAULT_FEU_USERNAME), 'p');

        $I->seeAPublishedUserAfterRegistration();
        $I->seeAUserWithInvalidatedToken();

        $email = Email::getByPath('/email/register-confirm');
        $I->seeEmailIsNotSent($email);

        $email = Email::getByPath('/email/register-confirmed');
        $I->seeEmailIsNotSent($email);
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testUserPropertiesAfterRegistration(FunctionalTester $I)
    {
        $this->register($I);

        $user = $I->grabOneUserAfterRegistration();
        $I->seePropertiesInFrontendUser($user, ['_user_locale']);
    }

    /**
     * @param FunctionalTester $I
     */
    private function register(FunctionalTester $I)
    {
        $I->amOnPage('/en/members/register');
        $I->fillField('form[name="members_user_registration_form"] input[type="email"][id="members_user_registration_form_email"]', MembersHelper::DEFAULT_FEU_EMAIL);
        $I->fillField('form[name="members_user_registration_form"] input[type="text"][id="members_user_registration_form_username"]', MembersHelper::DEFAULT_FEU_USERNAME);
        $I->fillField('form[name="members_user_registration_form"] input[type="password"][id="members_user_registration_form_plainPassword_first"]', 'password');
        $I->fillField('form[name="members_user_registration_form"] input[type="password"][id="members_user_registration_form_plainPassword_second"]', 'password');
        $I->click('Register');
    }
}