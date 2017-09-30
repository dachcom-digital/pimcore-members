<?php

namespace MembersBundle\Security;

use MembersBundle\Manager\RestrictionManager;
use MembersBundle\Manager\RestrictionManagerInterface;
use MembersBundle\Restriction\Restriction;
use Pimcore\Model;

class RestrictionUri
{
    const PROTECTED_ASSET_FOLDER = 'restricted-assets';

    const MEMBERS_REQUEST_URL = '/members/request-data/';

    /**
     * @var RestrictionManagerInterface
     */
    protected $restrictionManager;

    /**
     * RestrictionUri constructor.
     *
     * @param RestrictionManagerInterface $restrictionManager
     */
    public function __construct(RestrictionManagerInterface $restrictionManager)
    {
        $this->restrictionManager = $restrictionManager;
    }

    /**
     * @param string|Model\Asset $asset            if string, getByPath will be triggered.
     * @param bool|int           $objectProxyId    Sometimes, objects will be used for asset handling. eg. a download object with a asset href element.
     *                                             the object has restriction but the asset does not.
     *                                             If $objectProxyId is given, this method will check for the object restriction instead of the asset.
     * @param bool               $checkRestriction If true, this method will only return a valid string if current user is allowed to open the file.
     *
     * @return string
     * @throws \Exception
     */
    public function generateAssetUrl($asset = '', $objectProxyId = FALSE, $checkRestriction = FALSE)
    {
        $urlData = $this->getAssetData($asset, $objectProxyId, $checkRestriction);
        $url = empty($urlData) ? '' : $this->generateUrl([$urlData]);
        return $url;
    }

    /**
     * @see generateAssetUrl serves data as zip.
     *
     * @param bool  $checkRestriction If true, this method will only return a valid string if current user is allowed to open the file.
     * @param array $assetData        array( array('asset' => (Asset|string), 'objectProxyId' => FALSE|objectId) );
     *
     * @return string
     * @throws \Exception
     */
    public function generateAssetPackageUrl($assetData = [], $checkRestriction = FALSE)
    {
        if (!is_array($assetData)) {
            throw new \InvalidArgumentException('assetData has to be a array.');
        }

        $urlData = [];
        foreach ($assetData as $asset) {
            $url = $this->getAssetData($asset['asset'], isset($asset['objectProxyId']) ? $asset['objectProxyId'] : FALSE, $checkRestriction);
            if(empty($url)) {
                continue;
            }
            $urlData[] = $url;
        }

        return empty($urlData) ? '' : $this->generateUrl($urlData);
    }

    /**
     * Only for single asset url.
     * Get asset restriction groups and asset object by url fragment (d)
     *
     * @param string $urlFragment
     *
     * @return array|bool
     */
    public function getAssetUrlInformation($urlFragment)
    {
        $fileInfo = $this->parseUrlFragment($urlFragment);

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
        if ($userGroups === FALSE) {
            if (strpos($asset->getPath(), self::PROTECTED_ASSET_FOLDER) === FALSE) {
                $userGroups = NULL;
            }
        }

        $info['restrictionGroups'] = $userGroups;

        return $info;
    }

    /**
     * Decodes given Url
     *
     * @param $requestData
     *
     * @return array|bool
     */
    public function decodeAssetUrl($requestData)
    {
        $fileInfo = $this->parseUrlFragment($requestData);

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
            $object = $proxyId !== FALSE ? Model\DataObject\AbstractObject::getById($proxyId) : $asset;
            $restrictionElement = $this->restrictionManager->getElementRestrictionStatus($object);

            if ($restrictionElement->getSection() === RestrictionManager::RESTRICTION_SECTION_NOT_ALLOWED) {
                continue;
            }

            $dataToProcess[] = $asset;
        }

        if (count($dataToProcess) === 0) {
            return FALSE;
        }

        return $dataToProcess;
    }

    /**
     * @param string $asset
     * @param        $objectProxyId
     * @param        $checkRestriction
     *
     * @return array
     * @throws \Exception
     */
    private function getAssetData($asset = '', $objectProxyId = FALSE, $checkRestriction = FALSE)
    {
        if (is_string($asset)) {
            $asset = Model\Asset::getByPath($asset);
        }

        if (!$asset instanceof Model\Asset) {
            return [];
        }

        if (strpos($asset->getFullPath(), self::PROTECTED_ASSET_FOLDER) === FALSE) {
            throw new \Exception('Asset is not in protected environment: "' . $asset->getFullPath() . '". Please move asset to "' . self::PROTECTED_ASSET_FOLDER . '".');
        }

        if($checkRestriction === TRUE) {
            $restrictionElement = $this->restrictionManager->getElementRestrictionStatus($asset);
            if ($restrictionElement->getSection() === RestrictionManager::RESTRICTION_SECTION_NOT_ALLOWED) {
                return [];
            }
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
    private function generateUrl($data)
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
    private function parseUrlFragment($urlFragment)
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