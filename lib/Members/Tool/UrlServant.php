<?php

namespace Members\Tool;

use Members\Model\Restriction;
use Pimcore\API\Plugin\Exception;
use Pimcore\Model;

class UrlServant
{
    const PROTECTED_ASSET_FOLDER = 'restricted-assets';

    const MEMBERS_REQUEST_URL = '/members/request-data/';

    /**
     * @param string|Model\Asset $asset         if string, getByPath will be triggered.
     * @param bool|int           $objectProxyId Sometimes, objects will be used for asset handling. eg. a download object with a asset href element.
     *                                          the object has restriction but the asset does not.
     *                                          If $objectProxyId is given, this method will check for the object restriction instead of the asset.
     *
     * @return string
     * @throws \Exception
     */
    public static function generateAssetUrl($asset = '', $objectProxyId = FALSE)
    {
        $urlData = self::getAssetData($asset, $objectProxyId);
        return self::generateUrl([$urlData]);
    }

    /**
     * Only for single asset url.
     * Get asset restriction groups and asset object by url fragment (d)
     * @param string $urlFragment
     *
     * @return array|bool
     */
    public static function getAssetUrlInformation($urlFragment)
    {
        $fileInfo = self::parseUrlFragment($urlFragment);

        if (!is_array($fileInfo) || count($fileInfo) !== 1) {
            return FALSE;
        }

        $assetId = $fileInfo[0]->f;
        $asset = Model\Asset::getById($assetId);

        if (!$asset instanceof Model\Asset) {
            return FALSE;
        }

        $info = ['asset' => $asset, 'restrictionGroups' => FALSE];

        $restriction = FALSE;
        $userGroups = FALSE;

        try {
            $restriction = Restriction::getByTargetId($assetId, 'asset');
        } catch (\Exception $e) {
        }

        if ($restriction instanceof Restriction) {
            $userGroups = $restriction->getRelatedGroups();
        }

        //check if asset is maybe in restricted mode without any restriction settings
        //if not, set restriction to null since there is no restriction.
        if($userGroups === FALSE) {
            if(strpos($asset->getPath(), self::PROTECTED_ASSET_FOLDER) === FALSE) {
                $userGroups = NULL;
            }
        }

        $info['restrictionGroups'] = $userGroups;

        return $info;
    }

    /**
     * @see generateAssetUrl serves data as zip.
     *
     * @param array $assetData array( array('asset' => (Asset|string), 'objectProxyId' => FALSE|objectId) );
     *
     * @return string
     * @throws \Exception
     */
    public static function generateAssetPackageUrl($assetData = [])
    {
        if (!is_array($assetData)) {
            throw new Exception('assetData has to be a array.');
        }

        $urlData = [];
        foreach ($assetData as $asset) {
            $urlData[] = self::getAssetData($asset['asset'], isset($asset['objectProxyId']) ? $asset['objectProxyId'] : FALSE);
        }

        return self::generateUrl($urlData);
    }

    /**
     * Decodes given Url
     * @param $requestData
     *
     * @return array|bool
     */
    public static function decodeAssetUrl($requestData)
    {
        $fileInfo = self::parseUrlFragment($requestData);

        if (!is_array($fileInfo)) {
            return FALSE;
        }

        $dataToProcess = [];

        foreach ($fileInfo as $file) {
            $assetId = $file->f;
            $proxyId = $file->p;

            $asset = Model\Asset::getById($assetId);

            if (!$asset instanceof Model\Asset) {
                continue;
            }

            //proxy is available so asset is wrapped in some object data
            $object = $proxyId !== FALSE ? Model\Object\AbstractObject::getById($proxyId) : $asset;
            $restriction = $object instanceof Model\Object\AbstractObject ? Observer::isRestrictedObject($object) : Observer::isRestrictedAsset($asset);

            if ($restriction['section'] === Observer::SECTION_NOT_ALLOWED) {
                continue;
            }

            $dataToProcess[] = $asset;
        }

        return $dataToProcess;
    }

    /**
     * @param string $asset
     * @param        $objectProxyId
     *
     * @return array
     * @throws \Exception
     */
    private static function getAssetData($asset = '', $objectProxyId)
    {
        if (is_string($asset)) {
            $asset = Model\Asset::getByPath($asset);
        }

        if (strpos($asset->getFullPath(), self::PROTECTED_ASSET_FOLDER) === FALSE) {
            throw new \Exception('Asset is not in protected environment: "' . $asset->getFullPath() . '". Please move asset to "' . self::PROTECTED_ASSET_FOLDER . '".');
        }

        if (!$asset instanceof Model\Asset) {
            throw new \Exception('given data is not a asset.');
        }

        return [
            'f' => $asset->getId(),
            'p' => $objectProxyId !== FALSE ? (int)$objectProxyId : FALSE
        ];
    }

    /**
     * @param $data
     *
     * @return string
     */
    private static function generateUrl($data)
    {
        $data = json_encode($data);
        $base64 = base64_encode($data);
        $params = rtrim($base64, '=');

        return self::MEMBERS_REQUEST_URL . '' . $params;
    }

    /**
     * @param string $urlFragment
     *
     * @return array|bool
     */
    private static function parseUrlFragment($urlFragment)
    {
        $base64 = $urlFragment . str_repeat('=', strlen($urlFragment) % 4);
        $data = base64_decode($base64);
        $fileInfo = json_decode($data);

        if (!is_array($fileInfo)) {
            return FALSE;
        }

        return $fileInfo;
    }
}