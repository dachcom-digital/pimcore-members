<?php

namespace DachcomBundle\Test\acceptance\Restriction;

use DachcomBundle\Test\AcceptanceTester;

class AssetRestrictionCest
{
    public function testAssetFolderProtection(AcceptanceTester $I)
    {
        $asset = $I->haveAPimcoreAsset();
        $I->amOnPage($asset->getFullPath());
        $I->see('Forbidden', 'h1');
    }
}