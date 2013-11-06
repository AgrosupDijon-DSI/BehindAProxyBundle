<?php

namespace Cnerta\BehindAProxyBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder
            ->root('cnerta_behind_a_proxy')
                ->children()
                        ->scalarNode('enabled')->defaultFalse()->end()
                        ->scalarNode('host')->defaultValue(null)->end()
                        ->scalarNode('host_ssl')->defaultValue(null)->end()
                        ->scalarNode('port')->defaultValue(null)->end()
                        ->scalarNode('login')->defaultValue(null)->end()
                        ->scalarNode('password')->defaultValue(null)->end()
                        ->scalarNode('load_default_stream_context')->defaultValue(false)->end()
                    ->end()
                ->end()
                ;
        
        return $treeBuilder;
    }
}
