<?php

namespace MembersBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use MembersBundle\Configuration\Configuration as BundleConfiguration;
use Symfony\Component\Yaml\Yaml;

class MembersExtension extends Extension
{
    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator([__DIR__.'/../Resources/config']));
        $loader->load('services.yml');
        //$loader->load('profiler.yml');

        $configManagerDefinition = $container->getDefinition('members.configuration');
        $configManagerDefinition->addMethodCall('setConfig', [ $config ]);

        if(file_exists(BundleConfiguration::SYSTEM_CONFIG_FILE_PATH)) {
            $bundleConfig = Yaml::parse(file_get_contents(BundleConfiguration::SYSTEM_CONFIG_FILE_PATH));
            $configManagerDefinition->addMethodCall('setSystemConfig', [ $bundleConfig ]);
        }

        $container->setParameter('members.registration.event.type', $config['post_register_type']);
        $container->setParameter('members.resetting.retry_ttl', 7200);
        $container->setParameter('members.resetting.token_ttl', 8030);
    }
}