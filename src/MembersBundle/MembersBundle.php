<?php

namespace MembersBundle;

use MembersBundle\Security\OAuth\Dispatcher\ConnectDispatcher;
use MembersBundle\Security\OAuth\Dispatcher\Router\DispatchRouter;
use MembersBundle\Security\OAuth\Dispatcher\LoginDispatcher;
use MembersBundle\Tool\Install;
use MembersBundle\DependencyInjection\CompilerPass\OAuthLoginStrategyPass;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

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
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new OAuthLoginStrategyPass());

        $dispatcherDefinition = new Definition();
        $dispatcherDefinition->setClass(DispatchRouter::class);
        $dispatcherDefinition->setPublic(false);
        $dispatcherDefinition->setAutowired(true);
        $dispatcherDefinition->setAutoconfigured(true);

        foreach ([['connect', ConnectDispatcher::class], ['login', LoginDispatcher::class]] as $service) {
            $dispatcherDefinition->addMethodCall('register', [$service[0], new Reference($service[1])]);
        }

        $container->setDefinition(DispatchRouter::class, $dispatcherDefinition);
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
