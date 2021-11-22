<?php

namespace MembersBundle\Security;

use MembersBundle\Manager\RestrictionManager;
use MembersBundle\Manager\RestrictionManagerInterface;
use MembersBundle\Restriction\Restriction;
use Pimcore\Model;

class RestrictionUri
{
    public const PROTECTED_ASSET_FOLDER = 'restricted-assets';
    public const MEMBERS_REQUEST_URL = '/members/request-data/';

    protected RestrictionManagerInterface $restrictionManager;

    public function __construct(RestrictionManagerInterface $restrictionManager)
    {
        $this->restrictionManager = $restrictionManager;
    }

    /**
     * @param string|Model\Asset $asset            if string, getByPath will be triggered
     * @param bool|int           $objectProxyId    Sometimes, objects will be used for asset handling. eg. a download object with a asset href element.
     *                                             the object has restriction but the asset does not.
     *                                             If $objectProxyId is given, this method will check for the object restriction instead of the asset.
     * @param bool               $checkRestriction if true, this method will only return a valid string if current user is allowed to open the file
     *
     * @throws \Exception
     */
    public function generateAssetUrl(string|Model\Asset $asset = '', bool|int $objectProxyId = false, bool $checkRestriction = false): string
    {
        $urlData = $this->getAssetData($asset, $objectProxyId, $checkRestriction);

        return empty($urlData) ? '' : $this->generateUrl([$urlData]);
    }

    /**
     * @param array $assetData        array( array('asset' => (Asset|string), 'objectProxyId' => FALSE|objectId) );
     * @param bool  $checkRestriction if true, this method will only return a valid string if current user is allowed to open the file
     *
     * @throws \Exception
     * @see generateAssetUrl serves data as zip.
     *
     */
    public function generateAssetPackageUrl(array $assetData = [], bool $checkRestriction = false): string
    {
        $urlData = [];
        foreach ($assetData as $asset) {
            $url = $this->getAssetData($asset['asset'], $asset['objectProxyId'] ?? false, $checkRestriction);
            if (empty($url)) {
                continue;
            }
            $urlData[] = $url;
        }

        return empty($urlData) ? '' : $this->generateUrl($urlData);
    }

    /**
     * Only for single asset url.
     * Get asset restriction groups and asset object by url fragment (d).
     */
    public function getAssetUrlInformation(string $urlFragment): ?array
    {
        $fileInfo = $this->parseUrlFragment($urlFragment);

        if (!is_array($fileInfo) || count($fileInfo) !== 1) {
            return null;
        }

        $assetId = $fileInfo[0]['f'];
        $asset = Model\Asset::getById($assetId);

        if (!$asset instanceof Model\Asset) {
            return null;
        }

        $info = [
            'asset'             => $asset,
            'restrictionGroups' => []
        ];

        try {
            $restriction = Restriction::getByTargetId($assetId, 'asset');
        } catch (\Exception $e) {
            return null;
        }

        $userGroups = [];
        if ($restriction instanceof Restriction) {
            $userGroups = $restriction->getRelatedGroups();
        }

        //check if asset is maybe in restricted mode without any restriction settings
        //if not, set restriction to null since there is no restriction.
        if (!str_contains($asset->getPath(), self::PROTECTED_ASSET_FOLDER)) {
            $userGroups = [];
        }

        $info['restrictionGroups'] = $userGroups;

        return $info;
    }

    public function decodeAssetUrl(string $requestData): ?array
    {
        $fileInfo = $this->parseUrlFragment($requestData);

        if (!is_array($fileInfo)) {
            return null;
        }

        $dataToProcess = [];
        foreach ($fileInfo as $file) {
            $assetId = $file['f'];
            $proxyId = $file['p'];

            $asset = Model\Asset::getById($assetId);

            if (!$asset instanceof Model\Asset) {
                continue;
            }

            //proxy is available so asset is wrapped in some object data
            $object = $proxyId !== false ? Model\DataObject::getById($proxyId) : $asset;
            $restrictionElement = $this->restrictionManager->getElementRestrictionStatus($object);

            if ($restrictionElement->getSection() === RestrictionManager::RESTRICTION_SECTION_NOT_ALLOWED) {
                continue;
            }

            $dataToProcess[] = $asset;
        }

        if (count($dataToProcess) === 0) {
            return null;
        }

        return $dataToProcess;
    }

    /**
     * @throws \Exception
     */
    private function getAssetData(string|Model\Asset $asset = '', bool|int $objectProxyId = false, bool $checkRestriction = false): array
    {
        if (is_string($asset)) {
            $asset = Model\Asset::getByPath($asset);
        }

        if (!$asset instanceof Model\Asset) {
            return [];
        }

        if (!str_contains($asset->getFullPath(), self::PROTECTED_ASSET_FOLDER)) {
            throw new \Exception('Asset is not in protected environment: "' . $asset->getFullPath() . '". Please move asset to "' . self::PROTECTED_ASSET_FOLDER . '".');
        }

        if ($checkRestriction === true) {
            $restrictionElement = $this->restrictionManager->getElementRestrictionStatus($asset);
            if ($restrictionElement->getSection() === RestrictionManager::RESTRICTION_SECTION_NOT_ALLOWED) {
                return [];
            }
        }

        return [
            'f' => $asset->getId(),
            'p' => $objectProxyId !== false ? (int) $objectProxyId : false
        ];
    }

    private function generateUrl(array $data): string
    {
        $data = json_encode($data);
        $base64 = base64_encode($data);
        $params = rtrim($base64, '=');

        return self::MEMBERS_REQUEST_URL . '' . $params;
    }

    /**
     * @throws \JsonException
     */
    private function parseUrlFragment(string $urlFragment): ?array
    {
        $base64 = $urlFragment . str_repeat('=', strlen($urlFragment) % 4);
        $data = base64_decode($base64);
        $fileInfo = json_decode($data, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($fileInfo)) {
            return null;
        }

        return $fileInfo;
    }
}
