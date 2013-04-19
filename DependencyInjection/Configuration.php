<?php

namespace Ext\DirectBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
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
        $rootNode = $treeBuilder->root('direct');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        $rootNode->children()
            ->scalarNode('error_template')
                        ->defaultValue('ExtDirectBundle::extjs_errors.html.twig')
                    ->end()
            ->arrayNode('basic')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('type')
                        ->defaultValue('remoting')
                    ->end()
                    ->scalarNode('namespace')
                        ->defaultValue('Actions')
                    ->end()
                ->end()
        ->end();
        // ->arrayNode('annotations')
        //     ->addDefaultsIfNotSet()
        //     ->children()
        //         ->scalarNode('remote_attribute')
        //             ->defaultValue('@Remote')
        //         ->end()
        //         ->scalarNode('form_attribute')
        //             ->defaultValue('@Form')
        //         ->end()
        //     ->end()
        // ->end();

        $this->addRouterSection($rootNode);
        
        return $treeBuilder;
    }
    
    private function addRouterSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode->children()
            ->arrayNode('router')
                ->children()
                    ->scalarNode('resource')->end()
                ->end()
                ->children()
                    ->arrayNode('rules')
                        ->useAttributeAsKey('rules')
                        ->prototype('array')
                            ->children()
                                ->arrayNode('defaults')
                                    ->children()
                                        ->scalarNode('_controller')
                                            ->validate()
                                                ->ifTrue(function($v) {
                                                    return !preg_match('/^[\w]+(:[\w]+)?:[\w]+$/i', $v);
                                                })
                                                ->thenInvalid('This %s format is not supported')
                                            ->end()
                                            ->isRequired()
                                        ->end()
                                        ->booleanNode('params')->defaultFalse()->end()
                                        ->booleanNode('form')->defaultFalse()->end()
                                    ->end()
                                ->end()
                                ->arrayNode('reader')
                                ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('root')
                                            ->defaultNull()
                                        ->end()
                                        ->scalarNode('successProperty')
                                            ->defaultValue('success')
                                        ->end()
                                        ->scalarNode('totalProperty')
                                            ->defaultValue('total')
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }
}