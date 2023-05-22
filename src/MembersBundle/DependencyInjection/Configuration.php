<?php

namespace MembersBundle\DependencyInjection;

use MembersBundle\Form\Type\ChangePasswordFormType;
use MembersBundle\Form\Type\Sso\CompleteProfileFormType;
use MembersBundle\Form\Type\DeleteAccountFormType;
use MembersBundle\Form\Type\ProfileFormType;
use MembersBundle\Form\Type\RegistrationFormType;
use MembersBundle\Form\Type\LoginFormType;
use MembersBundle\Form\Type\ResettingFormType;
use MembersBundle\Form\Type\ResettingRequestFormType;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder()
    {
        $validPostRegisterTypes = ['confirm_by_mail', 'confirm_by_admin', 'confirm_instant'];

        $treeBuilder = new TreeBuilder('members');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->booleanNode('send_admin_mail_after_register')->defaultFalse()->end()
                ->booleanNode('send_user_mail_after_confirmed')->defaultFalse()->end()
                ->enumNode('post_register_type')
                    ->values($validPostRegisterTypes)
                    ->defaultValue('confirm_by_mail')
                ->end()
                ->enumNode('post_register_type_oauth')
                    ->values($validPostRegisterTypes)
                    ->defaultValue('confirm_instant')
                ->end()
                ->scalarNode('storage_path')->cannotBeEmpty()->defaultValue('/members')->end()
            ->end();

        $rootNode->append($this->buildOAuthNode());
        $rootNode->append($this->buildUserNode());
        $rootNode->append($this->buildGroupNode());
        $rootNode->append($this->buildSsoNode());
        $rootNode->append($this->buildAuthNode());
        $rootNode->append($this->buildRestrictionNode());
        $rootNode->append($this->buildRelationsNode());
        $rootNode->append($this->buildEmailNode());

        return $treeBuilder;
    }

    private function buildEmailNode(): NodeDefinition
    {
        $builder = new TreeBuilder('emails');
        $rootNode = $builder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('default')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('register_confirm')->isRequired()->defaultValue('/email/register-confirm')->end()
                        ->scalarNode('register_confirmed')->isRequired()->defaultValue('/email/register-confirmed')->cannotBeEmpty()->end()
                        ->scalarNode('register_password_resetting')->isRequired()->defaultValue('/email/password-reset')->cannotBeEmpty()->end()
                        ->scalarNode('admin_register_notification')->isRequired()->defaultValue('/email/admin-register-notification')->cannotBeEmpty()->end()
                    ->end()
                ->end()
                ->arrayNode('sites')
                    ->prototype('array')
                    ->children()
                        ->scalarNode('main_domain')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->arrayNode('emails')
                            ->children()
                                ->scalarNode('register_confirm')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('register_confirmed')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('register_password_resetting')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('admin_register_notification')->isRequired()->cannotBeEmpty()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $rootNode;
    }

    private function buildRelationsNode(): NodeDefinition
    {
        $builder = new TreeBuilder('relations');
        $rootNode = $builder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('login')
                    ->addDefaultsIfNotSet()
                    ->canBeUnset()
                    ->children()
                        ->arrayNode('form')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('type')->defaultValue(LoginFormType::class)->end()
                                ->scalarNode('name')->defaultValue('members_user_login_form')->end()
                                ->arrayNode('validation_groups')
                                    ->prototype('scalar')->end()
                                    ->defaultValue(['Login', 'Default'])
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('profile')
                    ->addDefaultsIfNotSet()
                    ->canBeUnset()
                    ->children()
                        ->arrayNode('form')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('type')->defaultValue(ProfileFormType::class)->end()
                                ->scalarNode('name')->defaultValue('members_user_profile_form')->end()
                                ->arrayNode('validation_groups')
                                    ->prototype('scalar')->end()
                                    ->defaultValue(['Profile', 'Default'])
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('change_password')
                    ->addDefaultsIfNotSet()
                    ->canBeUnset()
                        ->children()
                            ->arrayNode('form')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('type')->defaultValue(ChangePasswordFormType::class)->end()
                                ->scalarNode('name')->defaultValue('members_user_change_password_form')->end()
                                ->arrayNode('validation_groups')
                                    ->prototype('scalar')->end()
                                    ->defaultValue(['ChangePassword', 'Default'])
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('registration')
                    ->addDefaultsIfNotSet()
                    ->canBeUnset()
                        ->children()
                            ->arrayNode('form')
                            ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('type')->defaultValue(RegistrationFormType::class)->end()
                                    ->scalarNode('name')->defaultValue('members_user_registration_form')->end()
                                    ->arrayNode('validation_groups')
                                        ->prototype('scalar')->end()
                                        ->defaultValue(['Registration', 'Default'])
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('resetting_request')
                    ->addDefaultsIfNotSet()
                    ->canBeUnset()
                        ->children()
                            ->arrayNode('form')
                            ->addDefaultsIfNotSet()
                                ->children()
                                ->scalarNode('type')->defaultValue(ResettingRequestFormType::class)->end()
                                ->scalarNode('name')->defaultValue('members_user_resetting_request_form')->end()
                                ->arrayNode('validation_groups')
                                    ->prototype('scalar')->end()
                                    ->defaultValue(['ResetPassword', 'Default'])
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('resetting')
                    ->addDefaultsIfNotSet()
                    ->canBeUnset()
                        ->children()
                            ->scalarNode('retry_ttl')->defaultValue(7200)->end()
                            ->scalarNode('token_ttl')->defaultValue(86400)->end()
                            ->arrayNode('form')
                            ->addDefaultsIfNotSet()
                                ->children()
                                ->scalarNode('type')->defaultValue(ResettingFormType::class)->end()
                                ->scalarNode('name')->defaultValue('members_user_resetting_form')->end()
                                ->arrayNode('validation_groups')
                                    ->prototype('scalar')->end()
                                    ->defaultValue(['ResetPassword', 'Default'])
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('delete_account')
                    ->addDefaultsIfNotSet()
                    ->canBeUnset()
                        ->children()
                            ->arrayNode('form')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('type')->defaultValue(DeleteAccountFormType::class)->end()
                                ->scalarNode('name')->defaultValue('members_user_delete_account_form')->end()
                                ->arrayNode('validation_groups')
                                    ->prototype('scalar')->end()
                                    ->defaultValue(['DeleteAccount', 'Default'])
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('sso_identity_complete_profile')
                    ->addDefaultsIfNotSet()
                    ->canBeUnset()
                        ->children()
                            ->arrayNode('form')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('type')->defaultValue(CompleteProfileFormType::class)->end()
                                ->scalarNode('name')->defaultValue('members_user_sso_identity_complete_profile_form')->end()
                                ->arrayNode('validation_groups')
                                    ->prototype('scalar')->end()
                                    ->defaultValue(['CompleteProfile', 'Default'])
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $rootNode;
    }

    private function buildRestrictionNode(): NodeDefinition
    {
        $builder = new TreeBuilder('restriction');
        $rootNode = $builder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('enabled')->defaultFalse()->end()
                ->booleanNode('enable_public_asset_path_protection')->defaultFalse()->end()
                ->arrayNode('allowed_objects')
                    ->prototype('scalar')->end()
                    ->validate()
                        ->ifEmpty()
                        ->thenEmptyArray()
                    ->end()
                ->end()
            ->end();

        return $rootNode;
    }

    private function buildAuthNode(): NodeDefinition
    {
        $builder = new TreeBuilder('auth');
        $rootNode = $builder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('adapter')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class_name')->defaultNull()->end()
                        ->scalarNode('object_path')->defaultNull()->end()
                    ->end()
                ->end()
            ->end();

        return $rootNode;
    }

    private function buildSsoNode(): NodeDefinition
    {
        $builder = new TreeBuilder('sso');
        $rootNode = $builder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('adapter')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class_name')->defaultValue('SsoIdentity')->end()
                    ->end()
                ->end()
            ->end();

        return $rootNode;
    }

    private function buildGroupNode(): NodeDefinition
    {
        $builder = new TreeBuilder('group');
        $rootNode = $builder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('adapter')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class_name')->defaultValue('MembersGroup')->end()
                    ->end()
                ->end()
            ->end();

        return $rootNode;
    }

    private function buildUserNode(): NodeDefinition
    {
        $builder = new TreeBuilder('user');
        $rootNode = $builder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('adapter')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class_name')->defaultValue('MembersUser')->end()
                        ->scalarNode('object_key_form_field')->defaultValue('email')->end()
                    ->end()
                ->end()
                ->enumNode('auth_identifier')->values(['username', 'email'])->defaultValue('username')->end()
                ->booleanNode('only_auth_identifier_registration')->defaultFalse()->end()
                ->arrayNode('initial_groups')
                    ->prototype('scalar')->defaultValue([])->end()
                    ->validate()
                        ->ifEmpty()
                        ->thenEmptyArray()
                    ->end()
                ->end()
            ->end();

        return $rootNode;
    }

    private function buildOAuthNode(): NodeDefinition
    {
        $builder = new TreeBuilder('oauth');
        $rootNode = $builder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('enabled')->defaultFalse()->end()
                    ->enumNode('activation_type')
                        ->values(['complete_profile', 'instant'])
                        ->defaultValue('complete_profile')
                    ->end()
                    ->booleanNode('clean_up_expired_tokens')->defaultFalse()->end()
                    ->integerNode('expired_tokens_ttl')->defaultValue(0)->min(0)->end()
                    ->arrayNode('scopes')
                        ->useAttributeAsKey('client')
                        ->prototype('array')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
            ->end();

        return $rootNode;
    }
}

