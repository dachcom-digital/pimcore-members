<?php

namespace DachcomBundle\Test\functional\Restriction;

use Codeception\Exception\ModuleException;
use DachcomBundle\Test\FunctionalTester;

class AssetRestrictionCest
{
    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testAssetDownloadWithoutAuthorisation(FunctionalTester $I)
    {
        $group1 = $I->haveAFrontendUserGroup('group-1');
        $asset = $I->haveAPimcoreAsset();

        $I->addRestrictionToAsset($asset, [$group1->getId()]);

        $link = $I->haveASingleAssetDownloadLink($asset);

        $I->amOnPage($link);
        $I->canSeePageNotFound();
        $I->canSeeInTitle('invalid hash for asset request.');
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws ModuleException
     * @throws \Exception
     */
    public function testAssetDownloadWithoutAccessRights(FunctionalTester $I)
    {
        $group1 = $I->haveAFrontendUserGroup('group-1');
        $user = $I->haveARegisteredFrontEndUser(true);
        $asset = $I->haveAPimcoreAsset();

        $I->addRestrictionToAsset($asset, [$group1->getId()]);
        $I->amLoggedInAsFrontendUser($user);

        $link = $I->haveASingleAssetDownloadLink($asset);

        $I->amOnPage($link);
        $I->canSeePageNotFound();
        $I->canSeeInTitle('invalid hash for asset request.');
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws ModuleException
     * @throws \Exception
     */
    public function testAssetDownloadWithAuthorisation(FunctionalTester $I)
    {
        $group1 = $I->haveAFrontendUserGroup('group-1');
        $user = $I->haveARegisteredFrontEndUser(true, [$group1]);
        $asset = $I->haveAPimcoreAsset();

        $I->addRestrictionToAsset($asset, [$group1->getId()]);
        $I->amLoggedInAsFrontendUser($user);

        $link = $I->haveASingleAssetDownloadLink($asset);

        $I->seeDownloadLink($asset, $link);
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws ModuleException
     * @throws \Exception
     */
    public function testMultipleAssetDownloadWithAuthorisation(FunctionalTester $I)
    {
        $group1 = $I->haveAFrontendUserGroup('group-1');
        $user = $I->haveARegisteredFrontEndUser(true, [$group1]);
        $asset1 = $I->haveAPimcoreAsset('restricted-asset-1');
        $asset2 = $I->haveAPimcoreAsset('restricted-asset-2');

        $I->addRestrictionToAsset($asset1, [$group1->getId()]);
        $I->addRestrictionToAsset($asset2, [$group1->getId()]);
        $I->amLoggedInAsFrontendUser($user);

        $link = $I->haveAMultipleAssetDownloadLink([['asset' => $asset1], ['asset' => $asset2]]);

        $I->seeDownloadLinkZip('package.zip', $link);
    }
}