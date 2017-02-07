<?php

namespace Members\Tool;

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
}