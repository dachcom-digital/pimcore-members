<?php

namespace MembersBundle\DependencyInjection;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use MembersBundle\Configuration\Configuration as BundleConfiguration;

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

        $loader = new YamlFileLoader($container, new FileLocator([__DIR__ . '/../Resources/config']));
        $loader->load('services.yml');
        //$loader->load('profiler.yml');

        $configManagerDefinition = $container->getDefinition(BundleConfiguration::class);
        $configManagerDefinition->addMethodCall('setConfig', [$config]);

        if (file_exists(BundleConfiguration::SYSTEM_CONFIG_FILE_PATH)) {
            $bundleConfig = Yaml::parse(file_get_contents(BundleConfiguration::SYSTEM_CONFIG_FILE_PATH));
            $configManagerDefinition->addMethodCall('setSystemConfig', [$bundleConfig]);
        }

        $container->setParameter('members.registration.event.type', $config['post_register_type']);
        $container->setParameter('members.resetting.retry_ttl', $config['relations']['resetting']['retry_ttl']);
        $container->setParameter('members.resetting.token_ttl', $config['relations']['resetting']['token_ttl']);

        foreach ($config['relations']['login']['form'] as $confName => $confValue) {
            $container->setParameter('members_user.login.form.' . $confName, $confValue);
        }

        foreach ($config['relations']['profile']['form'] as $confName => $confValue) {
            $container->setParameter('members_user.profile.form.' . $confName, $confValue);
        }

        foreach ($config['relations']['change_password']['form'] as $confName => $confValue) {
            $container->setParameter('members_user.change_password.form.' . $confName, $confValue);
        }

        foreach ($config['relations']['registration']['form'] as $confName => $confValue) {
            $container->setParameter('members_user.registration.form.' . $confName, $confValue);
        }

        foreach ($config['relations']['resetting_request']['form'] as $confName => $confValue) {
            $container->setParameter('members_user.resetting_request.form.' . $confName, $confValue);
        }

        foreach ($config['relations']['resetting']['form'] as $confName => $confValue) {
            $container->setParameter('members_user.resetting.form.' . $confName, $confValue);
        }

        foreach ($config['relations']['delete_account']['form'] as $confName => $confValue) {
            $container->setParameter('members_user.delete_account.form.' . $confName, $confValue);
        }

    }
}