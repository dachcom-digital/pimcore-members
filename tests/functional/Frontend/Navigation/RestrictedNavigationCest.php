<?php

namespace DachcomBundle\Test\functional\Frontend\Navigation;

use DachcomBundle\Test\FunctionalTester;

class RestrictedNavigationCest
{
    /**
     * @param FunctionalTester $I
     */
    public function testNavigationWithoutLogin(FunctionalTester $I)
    {
        $group1 = $I->haveAFrontendUserGroup('group-1');

        $document1 = $I->haveAPageDocument('document-1', ['action' => 'navigation']);
        $document2 = $I->haveAPageDocument('document-2', ['action' => 'navigation']);

        $I->addRestrictionToDocument($document2, [$group1->getId()], true, false);

        $I->amOnPage('/document-1');

        $I->seeElement('div.nav a[title="document-1"]');
        $I->dontSeeElement('div.nav a[title="document-2"]');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testNavigationWithLogin(FunctionalTester $I)
    {
        $group1 = $I->haveAFrontendUserGroup('group-1');
        $user = $I->haveARegisteredFrontEndUser(true, [$group1]);

        $document1 = $I->haveAPageDocument('document-1', ['action' => 'navigation']);
        $document2 = $I->haveAPageDocument('document-2', ['action' => 'navigation']);

        $I->addRestrictionToDocument($document2, [$group1->getId()], true, false);

        $I->amLoggedInAsFrontendUser($user, 'members_fe');
        $I->amOnPage('/document-1');

        $I->seeElement('div.nav a[title="document-1"]');
        $I->seeElement('div.nav a[title="document-2"]');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testNavigationWithSwitchedUserLogin(FunctionalTester $I)
    {
        $group1 = $I->haveAFrontendUserGroup('group-1');
        $group2 = $I->haveAFrontendUserGroup('group-2');

        $document1 = $I->haveAPageDocument('document-1', ['action' => 'navigation']);
        $document2 = $I->haveAPageDocument('document-2', ['action' => 'navigation']);

        $user1 = $I->haveARegisteredFrontEndUser(true, [$group1]);
        $user2 = $I->haveARegisteredFrontEndUser(true, [$group2], ['email' => 'second@universe.org', 'userName' => 'norris']);

        $I->addRestrictionToDocument($document1, [$group1->getId()], true, false);
        $I->addRestrictionToDocument($document2, [$group2->getId()], true, false);

        $I->amLoggedInAsFrontendUser($user1, 'members_fe');
        $I->amOnPage('/document-1');

        $I->seeElement('div.nav a[title="document-1"]');
        $I->dontSeeElement('div.nav a[title="document-2"]');

        $I->amLoggedInAsFrontendUser($user2, 'members_fe');
        $I->amOnPage('/document-2');

        $I->seeElement('div.nav a[title="document-2"]');
        $I->dontSeeElement('div.nav a[title="document-1"]');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testLegacyNavigationWithoutLogin(FunctionalTester $I)
    {
        $group1 = $I->haveAFrontendUserGroup('group-1');

        $document1 = $I->haveAPageDocument('document-1', ['action' => 'navigation']);
        $document2 = $I->haveAPageDocument('document-2', ['action' => 'navigation']);

        $I->addRestrictionToDocument($document2, [$group1->getId()], true, false);

        $I->amOnPage('/document-1');

        $I->seeElement('div.legacy-nav a[title="document-1"]');
        $I->dontSeeElement('div.legacy-nav a[title="document-2"]');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testLegacyNavigationWithLogin(FunctionalTester $I)
    {
        $group1 = $I->haveAFrontendUserGroup('group-1');
        $user = $I->haveARegisteredFrontEndUser(true, [$group1]);

        $document1 = $I->haveAPageDocument('document-1', ['action' => 'navigation']);
        $document2 = $I->haveAPageDocument('document-2', ['action' => 'navigation']);

        $I->addRestrictionToDocument($document2, [$group1->getId()], true, false);

        $I->amLoggedInAsFrontendUser($user, 'members_fe');
        $I->amOnPage('/document-1');

        $I->seeElement('div.legacy-nav a[title="document-1"]');
        $I->seeElement('div.legacy-nav a[title="document-2"]');
    }

}