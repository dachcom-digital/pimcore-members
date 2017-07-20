<?php

namespace MembersBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('members');
        $rootNode
            ->children()
                ->booleanNode('send_admin_mail_after_register')->end()
                ->booleanNode('send_user_mail_after_confirmed')->end()
                ->scalarNode('post_register_type')->end()
                ->arrayNode('member')
                    ->isRequired()
                    ->children()
                        ->arrayNode('adapter')
                            ->children()
                                ->scalarNode('class_name')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('group')
                    ->isRequired()
                    ->children()
                        ->arrayNode('adapter')
                            ->children()
                                ->scalarNode('class_name')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('auth')
                    ->isRequired()
                        ->children()
                        ->arrayNode('adapter')
                            ->children()
                                ->scalarNode('class_name')->end()
                                ->scalarNode('object_path')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('restriction')
                    ->isRequired()
                    ->children()
                        ->arrayNode('allowed_objects')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('emails')
                    ->isRequired()
                    ->children()
                        ->scalarNode('register_confirm')->end()
                        ->scalarNode('register_confirmed')->end()
                        ->scalarNode('register_password_resetting')->end()
                        ->scalarNode('admin_register_notification')->end()
                    ->end()
                ->end()

            ->end()
        ;

        return $treeBuilder;
    }
}