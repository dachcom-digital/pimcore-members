<?php

namespace DachcomBundle\Test\Helper;

use Codeception\Module;
use MembersBundle\Restriction\Restriction;
use MembersBundle\Security\RestrictionUri;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\Document;
use Pimcore\Tests\Util\TestHelper;
use Symfony\Component\DependencyInjection\Container;

class MembersRestriction extends Module
{
    /**
     * @param string $objectType
     * @param string $key
     * @param null   $parent
     *
     * @return \Pimcore\Model\DataObject\Concrete|\Pimcore\Model\DataObject\Unittest
     * @throws \Exception
     */
    public function haveAPimcoreObject(string $objectType, $key = 'restricted-object', $parent = null)
    {
        $type = sprintf('\\Pimcore\\Model\\DataObject\\%s', $objectType);
        $object = TestHelper::createEmptyObject($key, true, false, $type);

        if ($parent !== null) {
            $object->setParent($parent);
        }

        $object->save();

        $this->assertInstanceOf($type, $object);

        return $object;
    }

    /**
     * @param AbstractObject $object
     * @param array          $groups
     * @param bool           $inherit
     * @param bool           $inherited
     */
    public function addRestrictionToObject(AbstractObject $object, $groups = [], $inherit = false, $inherited = false)
    {
        $restriction = $this->createElementRestriction($object, 'object', $groups, $inherit, $inherited);
        $this->assertInstanceOf(Restriction::class, $restriction);
    }

    /**
     * @param string $key
     *
     * @return Asset\Image
     * @throws \Exception
     */
    public function haveAPimcoreAsset($key = 'restricted-asset')
    {
        $asset = TestHelper::createImageAsset($key, false, false);
        $asset->setParent(Asset::getByPath('/' . RestrictionUri::PROTECTED_ASSET_FOLDER));
        $asset->save();

        $this->assertInstanceOf(Asset::class, $asset);

        return $asset;
    }

    /**
     * @param Asset $asset
     * @param array $groups
     * @param bool  $inherit
     * @param bool  $inherited
     */
    public function addRestrictionToAsset(Asset $asset, $groups = [], $inherit = false, $inherited = false)
    {
        $restriction = $this->createElementRestriction($asset, 'asset', $groups, $inherit, $inherited);
        $this->assertInstanceOf(Restriction::class, $restriction);
    }

    /**
     * @param string $key
     * @param null   $parent
     *
     * @return Document\Page
     * @throws \Exception
     */
    public function haveAPimcoreDocument($key = 'restricted-document', $parent = null)
    {
        $document = TestHelper::createEmptyDocumentPage($key, false);

        if ($parent !== null) {
            $document->setParent($parent);
        }

        $document->save();

        $this->assertInstanceOf(Document::class, $document);

        return $document;
    }

    /**
     * @param Document $document
     * @param array    $groups
     * @param bool     $inherit
     * @param bool     $inherited
     */
    public function addRestrictionToDocument(Document $document, $groups = [], $inherit = false, $inherited = false)
    {
        $restriction = $this->createElementRestriction($document, 'page', $groups, $inherit, $inherited);
        $this->assertInstanceOf(Restriction::class, $restriction);
    }

    /**
     * Actor Function to generate asset download link with containing a single asset file.
     *
     * @param Asset $asset
     *
     * @return string
     * @throws \Codeception\Exception\ModuleException
     * @throws \Exception
     */
    public function haveASingleAssetDownloadLink(Asset $asset)
    {
        $downloadLink = $this
            ->getContainer()->get(RestrictionUri::class)
            ->generateAssetUrl($asset);

        $this->assertInternalType('string', $downloadLink);

        return $downloadLink;
    }

    /**
     * Actor Function to generate asset download link with containing multiple assets.
     *
     * @param array $assets
     *
     * @return string
     * @throws \Codeception\Exception\ModuleException
     * @throws \Exception
     */
    public function haveAMultipleAssetDownloadLink(array $assets)
    {
        $downloadLink = $this
            ->getContainer()->get(RestrictionUri::class)
            ->generateAssetPackageUrl($assets);

        $this->assertInternalType('string', $downloadLink);

        return $downloadLink;
    }

    /**
     * @param        $element
     * @param string $type
     * @param array  $groups
     * @param bool   $inherit
     * @param bool   $inherited
     *
     * @return Restriction
     */
    protected function createElementRestriction(
        $element,
        string $type = 'page',
        array $groups = [],
        bool $inherit = false,
        bool $inherited = false
    ) {
        $restriction = new Restriction();
        $restriction->setTargetId($element->getId());
        $restriction->setCtype($type);
        $restriction->setInherit($inherit);
        $restriction->setIsInherited($inherited);
        $restriction->setRelatedGroups($groups);
        $restriction->save();

        return $restriction;

    }

    /**
     * @return Container
     * @throws \Codeception\Exception\ModuleException
     */
    protected function getContainer()
    {
        return $this->getModule('\\' . PimcoreCore::class)->getContainer();
    }
}
