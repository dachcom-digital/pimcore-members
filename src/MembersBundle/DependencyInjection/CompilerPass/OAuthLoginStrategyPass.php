<?php

namespace MembersBundle\DependencyInjection\CompilerPass;

use MembersBundle\Registry\OAuthLoginProcessorRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class OAuthLoginStrategyPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition(OAuthLoginProcessorRegistry::class);
        foreach ($container->findTaggedServiceIds('members.oauth.login_processor', true) as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition->addMethodCall('register', [new Reference($id), $attributes['identifier']]);
            }
        }
    }
}
