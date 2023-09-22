<?php

namespace DachcomBundle\Test\functional\Restriction;

use Codeception\Exception\ModuleException;
use DachcomBundle\Test\Support\FunctionalTester;

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
        $srParams = [
            'pattern'   => '/(\\w+)\\/members-test-route\\/(\\d+)$/',
            'reverse'   => '/%_locale/members-test-route/%object_id',
            'action'    => 'staticRoute',
            'variables' => '_locale,object_id',
        ];

        // we need to use unique static route names
        // @see https://github.com/pimcore/pimcore/pull/11126#issuecomment-1006733007

        $staticRoute = $I->haveAStaticRoute('test_route_1', $srParams);
        $classDefinition = $I->haveAPimcoreClass('TestClass');

        $group1 = $I->haveAFrontendUserGroup('group-1');
        $user = $I->haveARegisteredFrontEndUser(true);
        $object = $I->haveAPimcoreObject($classDefinition->getName(), 'object-1');

        $I->addRestrictionToObject($object, [$group1->getId()]);
        $I->amOnStaticRoute($staticRoute->getName(), ['_locale' => 'en', 'object_id' => $object->getId()]);
        $I->seeCurrentUrlMatches(sprintf('~/en/members/login\?_target_path=(.*)/en/members-test-route/%s~', $object->getId()));
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws ModuleException
     * @throws \Exception
     */
    public function testObjectRestrictionWithoutAccessRights(FunctionalTester $I)
    {
        $staticRoute = $I->haveAStaticRoute('test_route_2', $this->getStaticRouteConfig());
        $classDefinition = $I->haveAPimcoreClass('TestClass');

        $group1 = $I->haveAFrontendUserGroup('group-1');
        $user = $I->haveARegisteredFrontEndUser(true);
        $object = $I->haveAPimcoreObject($classDefinition->getName(), 'object-1');

        $I->addRestrictionToObject($object, [$group1->getId()]);
        $I->amLoggedInAsFrontendUser($user, 'members_fe');
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
        $staticRoute = $I->haveAStaticRoute('test_route_3', $this->getStaticRouteConfig());
        $classDefinition = $I->haveAPimcoreClass('TestClass');

        $group1 = $I->haveAFrontendUserGroup('group-1');
        $user = $I->haveARegisteredFrontEndUser(true, [$group1]);
        $object = $I->haveAPimcoreObject($classDefinition->getName(), 'object-1');

        $I->addRestrictionToObject($object, [$group1->getId()]);
        $I->amLoggedInAsFrontendUser($user, 'members_fe');
        $I->amOnStaticRoute($staticRoute->getName(), ['_locale' => 'en', 'object_id' => $object->getId()]);
        $I->see(sprintf('object id: %d', $object->getId()), '.static-route-debug');
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws ModuleException
     * @throws \Exception
     */
    public function testObjectInheritanceRestriction(FunctionalTester $I)
    {
        $group1 = $I->haveAFrontendUserGroup('group-1');
        $classDefinition = $I->haveAPimcoreClass('TestClass');

        $object = $I->haveAPimcoreObject($classDefinition->getName(), 'object-1');
        $subObject = $I->haveASubPimcoreObject($object, $classDefinition->getName(), 'sub-object-1');
        $subSubObject = $I->haveASubPimcoreObject($subObject, $classDefinition->getName(), 'sub-sub-object-1');

        $I->addRestrictionToObject($object, [$group1->getId()], true, false);

        $I->seeInheritedRestrictionOnEntity($subObject);
        $I->seeInheritedRestrictionOnEntity($subSubObject);
        $I->seeRestrictionWithGroupsOnEntity($subObject, [$group1]);
        $I->seeRestrictionWithGroupsOnEntity($subSubObject, [$group1]);

        $I->changeRestrictionToObject($object, [$group1->getId()], false, false);

        $I->seeRestrictionOnEntity($object);
        $I->seeNoRestrictionOnEntity($subObject);
        $I->seeNoRestrictionOnEntity($subSubObject);
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws ModuleException
     * @throws \Exception
     */
    public function testNewAddedObjectInheritanceRestriction(FunctionalTester $I)
    {
        $group1 = $I->haveAFrontendUserGroup('group-1');
        $classDefinition = $I->haveAPimcoreClass('TestClass');

        $object = $I->haveAPimcoreObject($classDefinition->getName(), 'object-1');
        $I->addRestrictionToObject($object, [$group1->getId()], true, false);

        $subObject = $I->haveASubPimcoreObject($object, $classDefinition->getName(), 'sub-object-1');
        $I->seeInheritedRestrictionOnEntity($subObject);
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws ModuleException
     * @throws \Exception
     */
    public function testMovedObjectInheritanceRestriction(FunctionalTester $I)
    {
        $group1 = $I->haveAFrontendUserGroup('group-1');
        $classDefinition = $I->haveAPimcoreClass('TestClass');

        $object1 = $I->haveAPimcoreObject($classDefinition->getName(), 'object-1');
        $object2 = $I->haveAPimcoreObject($classDefinition->getName(), 'object-2');

        $subObject = $I->haveASubPimcoreObject($object1, $classDefinition->getName(), 'sub-object-1');
        $subSubObject = $I->haveASubPimcoreObject($subObject, $classDefinition->getName(), 'sub-sub-object-1');

        $I->addRestrictionToObject($object1, [$group1->getId()], true, false);

        $I->seeInheritedRestrictionOnEntity($subObject);
        $I->seeInheritedRestrictionOnEntity($subSubObject);
        $I->moveObject($subObject, $object2);
        $I->seeNoRestrictionOnEntity($subObject);
        $I->seeNoRestrictionOnEntity($subSubObject);
    }

    /**
     * @return string[]
     */
    protected function getStaticRouteConfig()
    {
        return [
            'pattern'   => '/(\\w+)\\/members-test-route\\/(\\d+)$/',
            'reverse'   => '/%_locale/members-test-route/%object_id',
            'action'    => 'staticRouteAction',
            'variables' => '_locale,object_id',
        ];
    }
}
