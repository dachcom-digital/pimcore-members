<?php

namespace MembersBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;

class MembersBundle extends AbstractPimcoreBundle
{
    /**
     * {@inheritdoc}
     */
    public function getInstaller()
    {
        return $this->container->get('members.tool.installer');
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

    public function getCssPaths()
    {
        return [
            '/bundles/members/css/admin.css'
        ];
    }

}
