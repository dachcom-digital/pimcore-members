<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace MembersBundle\Twig\Extension;

use MembersBundle\Security\RestrictionUri;
use Pimcore\Model\Asset;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RestrictionUriExtension extends AbstractExtension
{
    public function __construct(protected RestrictionUri $restrictionUri)
    {
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
