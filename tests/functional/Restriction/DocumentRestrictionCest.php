<?php

namespace DachcomBundle\Test\functional\Restriction;

use Codeception\Exception\ModuleException;
use DachcomBundle\Test\FunctionalTester;

class DocumentRestrictionCest
{
    /**
     * @param FunctionalTester $I
     *
     * @throws ModuleException
     * @throws \Exception
     */
    public function testDocumentRestrictionWithoutAuthorization(FunctionalTester $I)
    {
        $group1 = $I->haveAFrontendUserGroup('group-1');
        $user = $I->haveARegisteredFrontEndUser(true);
        $document = $I->haveAPageDocument('document-1');

        $I->addRestrictionToDocument($document, [$group1->getId()]);
        $I->amOnPage($document->getFullPath());
        $I->seeCurrentUrlMatches(sprintf('~/en/members/login\?_target_path=(.*)/%s~', $document->getKey()));
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws ModuleException
     * @throws \Exception
     */
    public function testDocumentRestrictionWithoutAccessRights(FunctionalTester $I)
    {
        $group1 = $I->haveAFrontendUserGroup('group-1');
        $user = $I->haveARegisteredFrontEndUser(true);
        $document = $I->haveAPageDocument('document-1');

        $I->addRestrictionToDocument($document, [$group1->getId()]);
        $I->amLoggedInAsFrontendUser($user, 'members_fe');
        $I->amOnPage($document->getFullPath());
        $I->see('You have no access rights to view the requested page.', '.members.refused');
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws ModuleException
     * @throws \Exception
     */
    public function testDocumentRestrictionWithAuthorization(FunctionalTester $I)
    {
        $group1 = $I->haveAFrontendUserGroup('group-1');
        $user = $I->haveARegisteredFrontEndUser(true, [$group1]);
        $document = $I->haveAPageDocument('document-1');

        $I->addRestrictionToDocument($document, [$group1->getId()]);
        $I->amLoggedInAsFrontendUser($user, 'members_fe');
        $I->amOnPage($document->getFullPath());
        $I->see('Test Page for Members', 'title');
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws ModuleException
     * @throws \Exception
     */
    public function testDocumentInheritanceRestriction(FunctionalTester $I)
    {
        $group1 = $I->haveAFrontendUserGroup('group-1');

        $document = $I->haveAPageDocument('document-1');
        $subDocument = $I->haveASubPageDocument($document, 'sub-document-1');
        $subSubDocument = $I->haveASubPageDocument($subDocument, 'sub-sub-document-1');

        $I->addRestrictionToDocument($document, [$group1->getId()], true, false);

        $I->seeInheritedRestrictionOnEntity($subDocument);
        $I->seeInheritedRestrictionOnEntity($subSubDocument);
        $I->seeRestrictionWithGroupsOnEntity($subDocument, [$group1]);
        $I->seeRestrictionWithGroupsOnEntity($subSubDocument, [$group1]);

        $I->changeRestrictionToDocument($document, [$group1->getId()], false, false);

        $I->seeRestrictionOnEntity($document);
        $I->seeNoRestrictionOnEntity($subDocument);
        $I->seeNoRestrictionOnEntity($subSubDocument);
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws ModuleException
     * @throws \Exception
     */
    public function testNewAddedDocumentInheritanceRestriction(FunctionalTester $I)
    {
        $group1 = $I->haveAFrontendUserGroup('group-1');

        $document = $I->haveAPageDocument('document-1');
        $I->addRestrictionToDocument($document, [$group1->getId()], true, false);

        $subDocument = $I->haveASubPageDocument($document, 'sub-document-1');
        $I->seeInheritedRestrictionOnEntity($subDocument);
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws ModuleException
     * @throws \Exception
     */
    public function testMovedDocumentInheritanceRestriction(FunctionalTester $I)
    {
        $group1 = $I->haveAFrontendUserGroup('group-1');

        $document1 = $I->haveAPageDocument('document-1');
        $document2 = $I->haveAPageDocument('document-2');

        $subDocument = $I->haveASubPageDocument($document1, 'sub-document-1');
        $subSubDocument = $I->haveASubPageDocument($subDocument, 'sub-sub-document-1');

        $I->addRestrictionToDocument($document1, [$group1->getId()], true, false);

        $I->seeInheritedRestrictionOnEntity($subDocument);
        $I->seeInheritedRestrictionOnEntity($subSubDocument);
        $I->moveDocument($subDocument, $document2);
        $I->seeNoRestrictionOnEntity($subDocument);
        $I->seeNoRestrictionOnEntity($subSubDocument);
    }
}