<?php

namespace DachcomBundle\Test\Unit\Manager;

use DachcomBundle\Test\Support\Test\DachcomBundleTestCase;
use MembersBundle\Manager\RestrictionManager;
use MembersBundle\Restriction\ElementRestriction;

class RestrictionManagerTest extends DachcomBundleTestCase
{
    public function testGetElementRestrictedGroupsDefault(): void
    {
        $document = $this->createRestrictedDocument();
        $restrictionManager = $this->getContainer()->get(RestrictionManager::class);
        $restrictionGroups = $restrictionManager->getElementRestrictedGroups($document);

        $this->assertIsArray($restrictionGroups);
        $this->assertCount(1, $restrictionGroups);
        $this->assertContains('default', $restrictionGroups);
    }

    public function testGetElementRestrictedGroups(): void
    {
        $group = $this->createUserGroup();
        $document = $this->createRestrictedDocument([$group->getId()]);

        $restrictionManager = $this->getContainer()->get(RestrictionManager::class);
        $restrictionGroups = $restrictionManager->getElementRestrictedGroups($document);

        $this->assertIsArray($restrictionGroups);
        $this->assertCount(1, $restrictionGroups);
        $this->assertContains($group->getId(), $restrictionGroups);

    }

    public function testGetElementRestrictionStatus(): void
    {
        $group = $this->createUserGroup();
        $document = $this->createRestrictedDocument([$group->getId()]);

        $restrictionManager = $this->getContainer()->get(RestrictionManager::class);
        $restrictionStatus = $restrictionManager->getElementRestrictionStatus($document);

        $this->assertInstanceOf(ElementRestriction::class, $restrictionStatus);
        $this->assertEquals(RestrictionManager::RESTRICTION_STATE_NOT_LOGGED_IN, $restrictionStatus->getState());
        $this->assertEquals(RestrictionManager::RESTRICTION_SECTION_NOT_ALLOWED, $restrictionStatus->getSection());
        $this->assertEquals([$group->getId()], $restrictionStatus->getRestrictionGroups());
    }
}
