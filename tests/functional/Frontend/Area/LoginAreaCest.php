<?php

namespace DachcomBundle\Test\functional\Frontend\Area;

use Codeception\Exception\ModuleException;
use DachcomBundle\Test\FunctionalTester;
use DachcomBundle\Test\Util\MembersHelper;

class LoginAreaCest
{
    /**
     * @param FunctionalTester $I
     */
    public function testLoginAreaElementForm(FunctionalTester $I)
    {
        $editables = [
            'hideWhenLoggedIn'        => [
                'type'             => 'checkbox',
                'dataFromEditmode' => false
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

    /**
     * @param FunctionalTester $I
     *
     * @throws ModuleException
     */
    public function testLoginAreaElementWithDefaultSettingsAndInvalidCredentials(FunctionalTester $I)
    {
        $editables = [
            'hideWhenLoggedIn'        => [
                'type'             => 'checkbox',
                'dataFromEditmode' => false
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
        $I->see('Invalid credentials.', '.members.login.area div');

    }

    /**
     * @param FunctionalTester $I
     *
     * @throws ModuleException
     */
    public function testLoginAreaElementWithDefaultSettingsAndValidCredentials(FunctionalTester $I)
    {
        $editables = [
            'hideWhenLoggedIn'        => [
                'type'             => 'checkbox',
                'dataFromEditmode' => false
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

    /**
     * @param FunctionalTester $I
     *
     * @throws ModuleException
     */
    public function testLoginAreaElementWithHiddenAreaAfterLogin(FunctionalTester $I)
    {
        $editables = [
            'hideWhenLoggedIn'        => [
                'type'             => 'checkbox',
                'dataFromEditmode' => true
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

    /**
     * @param FunctionalTester $I
     *
     * @throws ModuleException
     */
    public function testLoginAreaElementWithRedirectToSpecificDocumentAfterSuccessfullyLogin(FunctionalTester $I)
    {
        $redirectDocument = $I->haveAPageDocument('success-document');
        $document = $I->haveAPageDocument('members-area-test');

        $editables = [
            'hideWhenLoggedIn'        => [
                'type'             => 'checkbox',
                'dataFromEditmode' => false
            ],
            'redirectAfterSuccess'    => [
                'type'             => 'relation',
                'dataFromEditmode' => [
                    'type'    => 'document',
                    'id'      => $redirectDocument->getId(),
                    'subtype' => $redirectDocument->getType()
                ]
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

    /**
     * @param FunctionalTester $I
     *
     * @throws ModuleException
     */
    public function testLoginAreaElementWithSnippetAfterSuccessfullyLogin(FunctionalTester $I)
    {
        $snippetParams = [
            'controller' => '@AppBundle\Controller\DefaultController',
            'action'     => 'snippet'
        ];

        $successSnippet = $I->haveASnippet('success-snippet', $snippetParams);
        $document = $I->haveAPageDocument('members-area-test');

        $editables = [
            'hideWhenLoggedIn'        => [
                'type'             => 'checkbox',
                'dataFromEditmode' => false
            ],
            'redirectAfterSuccess'    => [
                'type' => 'relation'
            ],
            'showSnippedWhenLoggedIn' => [
                'type'             => 'relation',
                'dataFromEditmode' => [
                    'type'    => 'document',
                    'id'      => $successSnippet->getId(),
                    'subtype' => $successSnippet->getType()
                ]
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