<?php

namespace DachcomBundle\Test\Functional\Frontend\Area;

use DachcomBundle\Test\Support\FunctionalTester;
use DachcomBundle\Test\Support\Util\MembersHelper;

class LoginAreaCest
{
    public function testLoginAreaElementForm(FunctionalTester $I): void
    {
        $editables = [
            'hideWhenLoggedIn'        => [
                'type'             => 'checkbox',
                'dataFromResource' => false
            ],
            'redirectAfterSuccess'    => [
                'type' => 'relation',
            ],
            'showSnippedWhenLoggedIn' => [
                'type' => 'relation',
            ],
        ];

        $document = $I->haveAPageDocument('members-area-test');

        $I->seeAnAreaElementPlacedOnDocument($document, 'members_login', $editables);

        $I->amOnPage('/members-area-test');
        $I->seeElement('div.members.login.area');
        $I->seeElement('form[class="members_user_login"]');
        $I->seeElement('form[class="members_user_login"] input[type="text"][id="_username"]');
        $I->seeElement('form[class="members_user_login"] input[type="password"][id="_password"]');
        $I->seeElement('form[class="members_user_login"] input[type="checkbox"][id="_remember_me"]');
        $I->seeElement('form[class="members_user_login"] button[type="submit"][id="_submit"]');
        $I->seeElement('form[class="members_user_login"] input[type="hidden"][id="_target_path"]');
        $I->seeElement('form[class="members_user_login"] input[type="hidden"][id="_failure_path"]');
    }

    public function testLoginAreaElementWithDefaultSettingsAndInvalidCredentials(FunctionalTester $I): void
    {
        $editables = [
            'hideWhenLoggedIn'        => [
                'type'             => 'checkbox',
                'dataFromResource' => false
            ],
            'redirectAfterSuccess'    => [
                'type' => 'relation',
            ],
            'showSnippedWhenLoggedIn' => [
                'type' => 'relation',
            ],
        ];

        $document = $I->haveAPageDocument('members-area-test');

        $I->seeAnAreaElementPlacedOnDocument($document, 'members_login', $editables);

        $I->amOnPage('/members-area-test');

        $I->fillField('form[class="members_user_login"] input[type="text"][id="_username"]', MembersHelper::DEFAULT_FEU_USERNAME);
        $I->fillField('form[class="members_user_login"] input[type="password"][id="_password"]', MembersHelper::DEFAULT_FEU_PASSWORD);
        $I->click('Log In');

        $I->seeANotLoggedInFrontEndUser();
        $I->see('Username could not be found.', '.members.login.area div');

    }

    public function testLoginAreaElementWithDefaultSettingsAndValidCredentials(FunctionalTester $I): void
    {
        $editables = [
            'hideWhenLoggedIn'        => [
                'type'             => 'checkbox',
                'dataFromResource' => false
            ],
            'redirectAfterSuccess'    => [
                'type' => 'relation',
            ],
            'showSnippedWhenLoggedIn' => [
                'type' => 'relation',
            ],
        ];

        $document = $I->haveAPageDocument('members-area-test');

        $I->haveARegisteredFrontEndUser(true);
        $I->seeAnAreaElementPlacedOnDocument($document, 'members_login', $editables);

        $I->amOnPage('/members-area-test');
        $I->seeElement(sprintf('form[class="members_user_login"] input[type="hidden"][id="_target_path"][value="%s"]', $document->getFullPath()));
        $I->seeElement(sprintf('form[class="members_user_login"] input[type="hidden"][id="_failure_path"][value="%s"]', $document->getFullPath()));

        $I->fillField('form[class="members_user_login"] input[type="text"][id="_username"]', MembersHelper::DEFAULT_FEU_USERNAME);
        $I->fillField('form[class="members_user_login"] input[type="password"][id="_password"]', MembersHelper::DEFAULT_FEU_PASSWORD);
        $I->click('Log In');

        $I->see('logout', 'a');

        $I->seeALoggedInFrontEndUser();
    }

    public function testLoginAreaElementWithHiddenAreaAfterLogin(FunctionalTester $I): void
    {
        $editables = [
            'hideWhenLoggedIn'        => [
                'type'             => 'checkbox',
                'dataFromResource' => true
            ],
            'redirectAfterSuccess'    => [
                'type' => 'relation',
            ],
            'showSnippedWhenLoggedIn' => [
                'type' => 'relation',
            ],
        ];

        $document = $I->haveAPageDocument('members-area-test');

        $I->haveARegisteredFrontEndUser(true);
        $I->seeAnAreaElementPlacedOnDocument($document, 'members_login', $editables);

        $I->amOnPage('/members-area-test');
        $I->seeElement(sprintf('form[class="members_user_login"] input[type="hidden"][id="_target_path"][value="%s"]', $document->getFullPath()));
        $I->seeElement(sprintf('form[class="members_user_login"] input[type="hidden"][id="_failure_path"][value="%s"]', $document->getFullPath()));

        $I->fillField('form[class="members_user_login"] input[type="text"][id="_username"]', MembersHelper::DEFAULT_FEU_USERNAME);
        $I->fillField('form[class="members_user_login"] input[type="password"][id="_password"]', MembersHelper::DEFAULT_FEU_PASSWORD);
        $I->click('Log In');

        $I->dontSee('logout', 'a');

        $I->seeALoggedInFrontEndUser();
    }

    public function testLoginAreaElementWithRedirectToSpecificDocumentAfterSuccessfullyLogin(FunctionalTester $I): void
    {
        $redirectDocument = $I->haveAPageDocument('success-document');
        $document = $I->haveAPageDocument('members-area-test');

        $editables = [
            'hideWhenLoggedIn'        => [
                'type'             => 'checkbox',
                'dataFromResource' => false
            ],
            'redirectAfterSuccess'    => [
                'type'             => 'relation',
                'dataFromResource' => serialize([
                    'type'    => 'document',
                    'id'      => $redirectDocument->getId(),
                    'subtype' => $redirectDocument->getType()
                ])
            ],
            'showSnippedWhenLoggedIn' => [
                'type' => 'relation',
            ],
        ];

        $I->haveARegisteredFrontEndUser(true);
        $I->seeAnAreaElementPlacedOnDocument($document, 'members_login', $editables);

        $I->amOnPage('/members-area-test');
        $I->seeElement(sprintf('form[class="members_user_login"] input[type="hidden"][id="_target_path"][value="%s"]', $redirectDocument->getFullPath()));

        $I->fillField('form[class="members_user_login"] input[type="text"][id="_username"]', MembersHelper::DEFAULT_FEU_USERNAME);
        $I->fillField('form[class="members_user_login"] input[type="password"][id="_password"]', MembersHelper::DEFAULT_FEU_PASSWORD);
        $I->click('Log In');

        $I->seeLastRequestIsInPath($redirectDocument->getFullPath());
    }

    public function testLoginAreaElementWithSnippetAfterSuccessfullyLogin(FunctionalTester $I): void
    {
        $snippetParams = [
            'controller' => 'App\Controller\DefaultController',
            'action'     => 'snippetAction'
        ];

        $successSnippet = $I->haveASnippet('success-snippet', $snippetParams);
        $document = $I->haveAPageDocument('members-area-test');

        $editables = [
            'hideWhenLoggedIn'        => [
                'type'             => 'checkbox',
                'dataFromResource' => false
            ],
            'redirectAfterSuccess'    => [
                'type' => 'relation'
            ],
            'showSnippedWhenLoggedIn' => [
                'type'             => 'relation',
                'dataFromResource' => serialize([
                    'type'    => 'document',
                    'id'      => $successSnippet->getId(),
                    'subtype' => $successSnippet->getType()
                ])
            ],
        ];

        $I->haveARegisteredFrontEndUser(true);
        $I->seeAnAreaElementPlacedOnDocument($document, 'members_login', $editables);

        $I->amOnPage('/members-area-test');

        $I->fillField('form[class="members_user_login"] input[type="text"][id="_username"]', MembersHelper::DEFAULT_FEU_USERNAME);
        $I->fillField('form[class="members_user_login"] input[type="password"][id="_password"]', MembersHelper::DEFAULT_FEU_PASSWORD);
        $I->click('Log In');

        $I->seeALoggedInFrontEndUser();

        $I->see(sprintf('snippet content with id %d', $successSnippet->getId()), '.snippet h3');

        $I->seePropertiesInLastFragmentRequest(['user', 'redirect_uri', 'logout_uri', 'current_uri']);
    }
}
