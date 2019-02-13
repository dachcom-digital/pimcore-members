<?php

namespace MembersBundle\Twig\Extension;

use Pimcore\Model\Asset;
use MembersBundle\Security\RestrictionUri;

class RestrictionUriExtension extends \Twig_Extension
{
    /**
     * @var RestrictionUri
     */
    private $restrictionUri;

    /**
     * @param RestrictionUri $restrictionUri
     */
    public function __construct(RestrictionUri $restrictionUri)
    {
        $this->restrictionUri = $restrictionUri;
    }

    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            new \Twig_Function('members_generate_asset_url', [$this, 'buildAssetUrl']),
            new \Twig_Function('members_generate_asset_package_url', [$this, 'buildAssetPackageUrl']),
        ];
    }

    /**
     * @param null $assetId
     * @param bool $checkRestriction
     *
     * @return string
     */
    public function buildAssetUrl($assetId = null, $checkRestriction = false)
    {
        $download = Asset::getById($assetId);
        if (!$download instanceof Asset) {
            return '';
        }

        return $this->restrictionUri->generateAssetUrl($download, false, $checkRestriction);
    }

    /**
     * @param array $assetIds
     * @param bool  $checkRestriction
     *
     * @return string
     */
    public function buildAssetPackageUrl(array $assetIds = [], $checkRestriction = false)
    {
        $packageData = [];
        foreach ($assetIds as $assetId) {
            $asset = Asset::getById($assetId);
            if (!$asset instanceof Asset) {
                continue;
            }
            $packageData[] = ['asset' => $asset];
        }

        $link = $this->restrictionUri->generateAssetPackageUrl($packageData, $checkRestriction);

        return $link;
    }
}
