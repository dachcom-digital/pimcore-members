<?php

namespace Members\Tool;

use Pimcore\API\Plugin\Exception;
use Pimcore\Model;

class UrlServant {

    const PROTECTED_ASSET_FOLDER = 'restricted-assets';

    const MEMBERS_REQUEST_URL = '/members/request-data/';

    /**
     * @param string            $assetPath      To ensure the privacy of the given asset, the assetPath is a required argument.
     * @param null|Model\Asset  $asset          optional. if asset is given, pass it through: performance! :)
     * @param bool|int          $objectProxyId  Sometimes, objects will be used for asset handling. eg. a download object with a asset href element.
     *                                          the object has restriction but the asset does not.
     *                                          If $objectProxyId is given, this method will check for the object restriction instead of the asset.
     * @return string
     * @throws \Exception
     */
    public static function generateAssetUrl( $assetPath, $asset = NULL, $objectProxyId = FALSE )
    {
        $urlData = self::getAssetData($assetPath, $asset, $objectProxyId);

        return self::generateUrl( array( $urlData ) );

    }

    /**
     * @see generateAssetUrl serves data as zip.
     * @param array $assetData array( array('assetPath' => '', 'asset' => null, 'objectProxyId' => FALSE|objectId) );
     *
     * @return string
     * @throws \Exception
     */
    public static function generateAssetPackageUrl( $assetData = array() )
    {
        if( !is_array($assetData ))
        {
            throw new Exception('assetData has to be a array.');
        }

        $urlData = array();

        foreach( $assetData as $asset )
        {
            $urlData[] = self::getAssetData($asset['assetPath'], isset( $asset['asset'] ) ? $asset['asset'] : NULL, isset( $asset['objectProxyId'] ) ? $asset['objectProxyId'] : FALSE);
        }

        return self::generateUrl( $urlData );
    }

    private static function getAssetData( $assetPath, $asset, $objectProxyId )
    {
        if( strpos($assetPath, self::PROTECTED_ASSET_FOLDER) === FALSE)
        {
            throw new \Exception('Asset is not in protected environment: "' . $assetPath . '". Please move asset to "' . self::PROTECTED_ASSET_FOLDER . '".');
        }

        if( is_null( $asset ) )
        {
            $asset = Model\Asset::getByPath( $assetPath );
        }

        if (!$asset instanceof Model\Asset)
        {
            throw new \Exception('given data is not a asset.');
        }

        return array(
            'f' => $asset->getId(),
            'p' => $objectProxyId !== FALSE ? (int) $objectProxyId : FALSE
        );

    }

    private static function generateUrl( $data )
    {
        $data = json_encode($data);
        $base64 = base64_encode($data);
        $params = rtrim($base64, '=');

        return self::MEMBERS_REQUEST_URL . '' . $params;

    }
}