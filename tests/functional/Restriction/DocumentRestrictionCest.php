<?php

namespace DachcomBundle\Test\functional\Restriction;

use DachcomBundle\Test\FunctionalTester;

class DocumentRestrictionCest
{
    /**
     * @param FunctionalTester $I
     *
     * @throws \Codeception\Exception\ModuleException
     * @throws \Exception
     */
    public function testDocumentRestrictionWithoutAuthorization(FunctionalTester $I)
    {
        $group1 = $I->haveAFrontendUserGroup('group-1');
        $user = $I->haveARegisteredFrontEndUser(true);
        $document = $I->haveAPimcoreDocument('document-1');

        $I->addRestrictionToDocument($document, [$group1->getId()]);
        $I->amOnPage($document->getFullPath());
        $I->seeCurrentUrlMatches(sprintf('~/en/members/login\?_target_path=(.*)localhost/%s~', $document->getKey()));
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws \Codeception\Exception\ModuleException
     * @throws \Exception
     */
    public function testDocumentRestrictionWithoutAccessRights(FunctionalTester $I)
    {
        $group1 = $I->haveAFrontendUserGroup('group-1');
        $user = $I->haveARegisteredFrontEndUser(true);
        $document = $I->haveAPimcoreDocument('document-1');

        $I->addRestrictionToDocument($document, [$group1->getId()]);
        $I->amLoggedInAsFrontendUser($user);
        $I->amOnPage($document->getFullPath());
        $I->see('You have no access rights to view the requested page.', '.members.refused');
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws \Codeception\Exception\ModuleException
     * @throws \Exception
     */
    public function testDocumentRestrictionWithAuthorization(FunctionalTester $I)
    {
        $group1 = $I->haveAFrontendUserGroup('group-1');
        $user = $I->haveARegisteredFrontEndUser(true, [$group1]);
        $document = $I->haveAPimcoreDocument('document-1');

        $I->addRestrictionToDocument($document, [$group1->getId()]);
        $I->amLoggedInAsFrontendUser($user);
        $I->amOnPage($document->getFullPath());
        $I->see('Test Page for Members', 'title');
    }

}