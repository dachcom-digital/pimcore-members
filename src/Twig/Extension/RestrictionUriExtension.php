<?php

namespace MembersBundle\Twig\Extension;

use Pimcore\Model\Asset;
use MembersBundle\Security\RestrictionUri;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RestrictionUriExtension extends AbstractExtension
{
    protected RestrictionUri $restrictionUri;

    public function __construct(RestrictionUri $restrictionUri)
    {
        $this->restrictionUri = $restrictionUri;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('members_generate_asset_url', [$this, 'buildAssetUrl']),
            new TwigFunction('members_generate_asset_package_url', [$this, 'buildAssetPackageUrl']),
        ];
    }

    /**
     * @throws \Exception
     */
    public function buildAssetUrl($assetId = null, bool $checkRestriction = false): string
    {
        $download = Asset::getById($assetId);
        if (!$download instanceof Asset) {
            return '';
        }

        return $this->restrictionUri->generateAssetUrl($download, false, $checkRestriction);
    }

    /**
     * @throws \Exception
     */
    public function buildAssetPackageUrl(array $assetIds = [], bool $checkRestriction = false): string
    {
        $packageData = [];
        foreach ($assetIds as $assetId) {
            $asset = Asset::getById($assetId);
            if (!$asset instanceof Asset) {
                continue;
            }
            $packageData[] = ['asset' => $asset];
        }

        return $this->restrictionUri->generateAssetPackageUrl($packageData, $checkRestriction);
    }
}
