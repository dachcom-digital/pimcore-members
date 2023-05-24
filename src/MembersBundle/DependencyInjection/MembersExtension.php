<?php

namespace MembersBundle\DependencyInjection;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Security\Authenticator\OAuthIdentityAuthenticator;
use MembersBundle\Security\Encoder\Factory\UserAwareEncoderFactory;
use MembersBundle\Security\OAuth\Dispatcher\ConnectDispatcher;
use MembersBundle\Security\OAuth\Dispatcher\LoginDispatcher;
use MembersBundle\Security\OAuth\Dispatcher\Router\DispatchRouter;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use MembersBundle\Configuration\Configuration as BundleConfiguration;

class MembersExtension extends Extension implements PrependExtensionInterface
{
    public function prepend(ContainerBuilder $container): void
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration($this->getConfiguration([], $container), $configs);

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

        $loader = new YamlFileLoader($container, new FileLocator([__DIR__ . '/../Resources/config']));
        $loader->load('services.yml');

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
            $loader->load('oauth.yml');
            $this->enableOauth($container, $config);
        }

        $this->buildSecurityConfiguration($container, $loader);
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
        if ($this->authenticatorIsEnabled($container) === false) {

            $container->loadFromExtension('pimcore', [
                'security' => [
                    'encoder_factories' => [
                        UserInterface::class => UserAwareEncoderFactory::class
                    ]
                ]
            ]);

            if ($oauthEnabled === true) {
                $container->loadFromExtension('security', [
                    'firewalls' => [
                        'members_fe' => [
                            'guard' => [
                                'authenticators' => [
                                    \MembersBundle\Security\OAuthIdentityAuthenticator::class
                                ]
                            ]
                        ]
                    ]
                ]);
            }

            return;
        }

        $container->loadFromExtension('pimcore', [
            'security' => [
                'password_hasher_factories' => [
                    UserInterface::class => 'members.security.password_hasher_factory'
                ]
            ]
        ]);

        if ($oauthEnabled === true) {
            $container->loadFromExtension('security', [
                'firewalls' => [
                    'members_fe' => [
                        'custom_authenticators' => [
                            OAuthIdentityAuthenticator::class
                        ]
                    ]
                ]
            ]);
        }
    }

    protected function buildSecurityConfiguration(ContainerBuilder $container, YamlFileLoader $loader): void
    {
        if ($this->authenticatorIsEnabled($container) === false) {
            $loader->load('security_legacy.yml');

            return;
        }

        $loader->load('security_authenticator_manager.yml');
    }

    protected function authenticatorIsEnabled(ContainerBuilder $container): bool
    {
        if (!$container->hasParameter('security.authenticator.manager.enabled')) {
            return false;
        }

        if ($container->getParameter('security.authenticator.manager.enabled') !== true) {
            return false;
        }

        return true;
    }
}
