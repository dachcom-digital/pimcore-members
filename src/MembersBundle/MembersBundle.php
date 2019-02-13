<?php

namespace MembersBundle;

use MembersBundle\Tool\Install;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;

/**
 * Class MembersBundle.
 */
class MembersBundle extends AbstractPimcoreBundle
{
    use PackageVersionTrait;
    const PACKAGE_NAME = 'dachcom-digital/members';

    /**
     * {@inheritdoc}
     */
    public function getInstaller()
    {
        return $this->container->get(Install::class);
    }

    /**
     * @return string[]
     */
    public function getJsPaths()
    {
        return [
            '/bundles/members/js/backend/startup.js',
            '/bundles/members/js/backend/document/restriction.js',
            '/bundles/members/js/pimcore/js/coreExtension/data/dataMultiselect.js',
            '/bundles/members/js/pimcore/js/coreExtension/data/membersGroupMultiselect.js',
            '/bundles/members/js/pimcore/js/coreExtension/tags/multiselect.js',
            '/bundles/members/js/pimcore/js/coreExtension/tags/membersGroupMultiselect.js'
        ];
    }

    /**
     * @return array
     */
    public function getCssPaths()
    {
        return [
            '/bundles/members/css/admin.css'
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getComposerPackageName(): string
    {
        return self::PACKAGE_NAME;
    }
}
