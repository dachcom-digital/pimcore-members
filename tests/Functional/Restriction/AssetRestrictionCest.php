<?php

namespace DachcomBundle\Test\Functional\Restriction;

use Codeception\Exception\ModuleException;
use DachcomBundle\Test\Support\FunctionalTester;

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

        $restrictedFolder = $I->haveAProtectedAssetFolder();
        $asset = $I->haveAPimcoreAsset('bundle-asset-test', ['parent' => $restrictedFolder]);

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

        $restrictedFolder = $I->haveAProtectedAssetFolder();
        $asset = $I->haveAPimcoreAsset('bundle-asset-test', ['parent' => $restrictedFolder]);

        $I->addRestrictionToAsset($asset, [$group1->getId()]);
        $I->amLoggedInAsFrontendUser($user, 'members_fe');

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

        $restrictedFolder = $I->haveAProtectedAssetFolder();
        $asset = $I->haveAPimcoreAsset('bundle-asset-test', ['parent' => $restrictedFolder]);

        $I->addRestrictionToAsset($asset, [$group1->getId()]);
        $I->amLoggedInAsFrontendUser($user, 'members_fe');

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
        $restrictedFolder = $I->haveAProtectedAssetFolder();

        $asset1 = $I->haveAPimcoreAsset('restricted-asset-1', ['parent' => $restrictedFolder]);
        $asset2 = $I->haveAPimcoreAsset('restricted-asset-2', ['parent' => $restrictedFolder]);

        $I->addRestrictionToAsset($asset1, [$group1->getId()]);
        $I->addRestrictionToAsset($asset2, [$group1->getId()]);
        $I->amLoggedInAsFrontendUser($user, 'members_fe');

        $link = $I->haveAMultipleAssetDownloadLink([['asset' => $asset1], ['asset' => $asset2]]);

        $I->seeDownloadLinkZip('package.zip', $link);
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws ModuleException
     * @throws \Exception
     */
    public function testAssetInheritanceRestriction(FunctionalTester $I)
    {
        $group1 = $I->haveAFrontendUserGroup('group-1');
        $restrictedFolder = $I->haveAProtectedAssetFolder();

        $assetFolder = $I->haveASubPimcoreAssetFolder($restrictedFolder, 'sub-folder');
        $subAsset = $I->haveASubPimcoreAsset($assetFolder, 'sub-asset-1');

        $I->addRestrictionToAsset($assetFolder, [$group1->getId()], true, false);

        $I->seeInheritedRestrictionOnEntity($subAsset);
        $I->seeRestrictionWithGroupsOnEntity($subAsset, [$group1]);

        $I->changeRestrictionToAsset($assetFolder, [$group1->getId()], false, false);

        $I->seeRestrictionOnEntity($assetFolder);
        $I->seeNoRestrictionOnEntity($subAsset);
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws ModuleException
     * @throws \Exception
     */
    public function testNewAddedAssetInheritanceRestriction(FunctionalTester $I)
    {
        $group1 = $I->haveAFrontendUserGroup('group-1');
        $restrictedFolder = $I->haveAProtectedAssetFolder();

        $assetFolder = $I->haveASubPimcoreAssetFolder($restrictedFolder, 'sub-folder');

        $I->addRestrictionToAsset($assetFolder, [$group1->getId()], true, false);

        $subAsset = $I->haveASubPimcoreAsset($assetFolder, 'sub-asset-1');
        $I->seeInheritedRestrictionOnEntity($subAsset);
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws ModuleException
     * @throws \Exception
     */
    public function testMovedAssetInheritanceRestriction(FunctionalTester $I)
    {
        $group1 = $I->haveAFrontendUserGroup('group-1');
        $restrictedFolder = $I->haveAProtectedAssetFolder();

        $assetFolder1 = $I->haveASubPimcoreAssetFolder($restrictedFolder, 'sub-folder-1');
        $assetFolder2 = $I->haveASubPimcoreAssetFolder($restrictedFolder, 'sub-folder-2');

        $subAsset1 = $I->haveASubPimcoreAsset($assetFolder1, 'sub-asset-1');

        $I->addRestrictionToAsset($assetFolder1, [$group1->getId()], true, false);

        $I->seeInheritedRestrictionOnEntity($subAsset1);
        $I->moveAsset($subAsset1, $assetFolder2);
        $I->seeNoRestrictionOnEntity($subAsset1);
    }
}