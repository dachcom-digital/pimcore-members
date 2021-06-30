<?php

namespace MembersBundle;

use MembersBundle\DependencyInjection\CompilerPass\OAuthLoginStrategyPass;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;
use Pimcore\HttpKernel\Bundle\DependentBundleInterface;
use Pimcore\HttpKernel\BundleCollection\BundleCollection;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MembersBundle extends AbstractPimcoreBundle implements DependentBundleInterface
{
    use PackageVersionTrait;

    const PACKAGE_NAME = 'dachcom-digital/members';

    /**
     * {@inheritdoc}
     */
    public function getInstaller()
    {
        return null;
    }

    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new OAuthLoginStrategyPass());
    }

    /**
     * {@inheritdoc}
     */
    public static function registerDependentBundles(BundleCollection $collection)
    {
        if (class_exists('\\KnpU\\OAuth2ClientBundle\\KnpUOAuth2ClientBundle')) {
            $collection->addBundle(new \KnpU\OAuth2ClientBundle\KnpUOAuth2ClientBundle());
        }
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
