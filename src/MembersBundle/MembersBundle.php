<?php

namespace MembersBundle;

use MembersBundle\Tool\Install;
use MembersBundle\DependencyInjection\CompilerPass\OAuthLoginStrategyPass;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;
use Pimcore\HttpKernel\Bundle\DependentBundleInterface;
use Pimcore\HttpKernel\BundleCollection\BundleCollection;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MembersBundle extends AbstractPimcoreBundle implements DependentBundleInterface
{
    use PackageVersionTrait;

    public const PACKAGE_NAME = 'dachcom-digital/members';

    public function getInstaller(): Install
    {
        return $this->container->get(Install::class);
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new OAuthLoginStrategyPass());
    }

    public static function registerDependentBundles(BundleCollection $collection): void
    {
        if (class_exists('\\KnpU\\OAuth2ClientBundle\\KnpUOAuth2ClientBundle')) {
            $collection->addBundle(new \KnpU\OAuth2ClientBundle\KnpUOAuth2ClientBundle());
        }
    }

    public function getJsPaths(): array
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

    public function getCssPaths(): array
    {
        return [
            '/bundles/members/css/admin.css'
        ];
    }

    public function getEditmodeCssPaths(): array
    {
        return [
            '/bundles/members/css/admin-editmode.css',
        ];
    }

    protected function getComposerPackageName(): string
    {
        return self::PACKAGE_NAME;
    }
}
