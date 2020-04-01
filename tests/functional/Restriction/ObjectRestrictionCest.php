<?php

namespace DachcomBundle\Test\functional\Restriction;

use Codeception\Exception\ModuleException;
use DachcomBundle\Test\FunctionalTester;

class ObjectRestrictionCest
{
    /**
     * @param FunctionalTester $I
     *
     * @throws ModuleException
     * @throws \Exception
     */
    public function testObjectRestrictionWithoutAuthorization(FunctionalTester $I)
    {
        $classDefinition = $I->haveAPimcoreClass('TestClass');
        $staticRoute = $I->haveAStaticRoute('test_route');

        $group1 = $I->haveAFrontendUserGroup('group-1');
        $user = $I->haveARegisteredFrontEndUser(true);
        $object = $I->haveAPimcoreObject($classDefinition->getName(), 'object-1');

        $I->addRestrictionToObject($object, [$group1->getId()]);
        $I->amOnStaticRoute($staticRoute->getName(), ['_locale' => 'en', 'object_id' => $object->getId()]);
        $I->seeCurrentUrlMatches(sprintf('~/en/members/login\?_target_path=(.*)localhost/en/members-test-route/%s~', $object->getId()));
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws ModuleException
     * @throws \Exception
     */
    public function testObjectRestrictionWithoutAccessRights(FunctionalTester $I)
    {
        $classDefinition = $I->haveAPimcoreClass('TestClass');
        $staticRoute = $I->haveAStaticRoute('test_route');

        $group1 = $I->haveAFrontendUserGroup('group-1');
        $user = $I->haveARegisteredFrontEndUser(true);
        $object = $I->haveAPimcoreObject($classDefinition->getName(), 'object-1');

        $I->addRestrictionToObject($object, [$group1->getId()]);
        $I->amLoggedInAsFrontendUser($user);
        $I->amOnStaticRoute($staticRoute->getName(), ['_locale' => 'en', 'object_id' => $object->getId()]);
        $I->see('You have no access rights to view the requested page.', '.members.refused');
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws ModuleException
     * @throws \Exception
     */
    public function testObjectRestrictionWithAuthorization(FunctionalTester $I)
    {
        $classDefinition = $I->haveAPimcoreClass('TestClass');
        $staticRoute = $I->haveAStaticRoute('test_route');

        $group1 = $I->haveAFrontendUserGroup('group-1');
        $user = $I->haveARegisteredFrontEndUser(true, [$group1]);
        $object = $I->haveAPimcoreObject($classDefinition->getName(), 'object-1');

        $I->addRestrictionToObject($object, [$group1->getId()]);
        $I->amLoggedInAsFrontendUser($user);
        $I->amOnStaticRoute($staticRoute->getName(), ['_locale' => 'en', 'object_id' => $object->getId()]);
        $I->see(sprintf('object id: %d', $object->getId()), '.static-route-debug');
    }
}
