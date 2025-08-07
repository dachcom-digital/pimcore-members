<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace MembersBundle\DependencyInjection;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Configuration\Configuration as BundleConfiguration;
use MembersBundle\Security\Authenticator\OAuthIdentityAuthenticator;
use MembersBundle\Security\OAuth\Dispatcher\ConnectDispatcher;
use MembersBundle\Security\OAuth\Dispatcher\LoginDispatcher;
use MembersBundle\Security\OAuth\Dispatcher\Router\DispatchRouter;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class MembersExtension extends Extension implements PrependExtensionInterface
{
    public function prepend(ContainerBuilder $container): void
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration($this->getConfiguration([], $container), $configs);

        /* @phpstan-ignore-next-line */
        if (!$container->hasParameter('members.firewall_name')) {
            $container->setParameter('members.firewall_name', 'members_fe');
        }

        $oauthEnabled = false;
        if ($container->hasExtension('security') === true && $config['oauth']['enabled'] === true) {
            $oauthEnabled = true;
        }

        $this->extendPimcoreSecurityConfiguration($container, $oauthEnabled);
    }

    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator([__DIR__ . '/../../config']));
        $loader->load('services.yaml');

        $configManagerDefinition = $container->getDefinition(BundleConfiguration::class);
        $configManagerDefinition->addMethodCall('setConfig', [$config]);

        $container->setParameter('members.registration.event.type', $config['post_register_type']);
        $container->setParameter('members.registration.event.oauth_type', $config['post_register_type_oauth']);
        $container->setParameter('members.resetting.retry_ttl', $config['relations']['resetting']['retry_ttl']);
        $container->setParameter('members.resetting.token_ttl', $config['relations']['resetting']['token_ttl']);
        $container->setParameter('members.auth.identifier', $config['user']['auth_identifier']);
        $container->setParameter('members.auth.only_auth_identifier_registration', $config['user']['only_auth_identifier_registration']);

        $container->setParameter('members.oauth.enabled', $config['oauth']['enabled']);
        $container->setParameter('members.oauth.clean_up_expired_tokens', $config['oauth']['clean_up_expired_tokens']);
        $container->setParameter('members.oauth.expired_tokens_ttl', $config['oauth']['expired_tokens_ttl']);

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

        if ($config['oauth']['enabled']) {
            $container->setParameter('members.oauth.scopes', $config['oauth']['scopes']);
            $loader->load('oauth.yaml');
            $this->enableOauth($container, $config);
        }

        $loader->load('security.yaml');
    }

    protected function enableOauth(ContainerBuilder $container, array $config): void
    {
        $dispatcherDefinition = new Definition();
        $dispatcherDefinition->setClass(DispatchRouter::class);
        $dispatcherDefinition->setPublic(false);
        $dispatcherDefinition->setAutowired(true);
        $dispatcherDefinition->setAutoconfigured(true);

        foreach ([['connect', ConnectDispatcher::class], ['login', LoginDispatcher::class]] as $service) {
            $dispatcherDefinition->addMethodCall('register', [$service[0], new Reference($service[1])]);
        }

        $container->setDefinition(DispatchRouter::class, $dispatcherDefinition);

        foreach ($config['relations']['sso_identity_complete_profile']['form'] as $confName => $confValue) {
            $container->setParameter('members_user.oauth.sso_identity_complete_profile.form.' . $confName, $confValue);
        }
    }

    protected function extendPimcoreSecurityConfiguration(ContainerBuilder $container, bool $oauthEnabled): void
    {
        $firewallName = $container->getParameter('members.firewall_name');

        // Configure password hashers for Pimcore DataObjects
        $container->loadFromExtension('security', [
            'password_hashers' => [
                'Pimcore\Model\DataObject\MembersUser' => 'members.security.pimcore_password_hasher',
                UserInterface::class => 'members.security.pimcore_password_hasher'
            ]
        ]);

        if ($oauthEnabled === true) {
            $container->loadFromExtension('security', [
                'firewalls' => [
                    $firewallName => [
                        'custom_authenticators' => [
                            OAuthIdentityAuthenticator::class
                        ]
                    ]
                ]
            ]);
        }
    }
}
