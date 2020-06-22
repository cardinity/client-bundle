<?php

namespace Cardinity\ClientBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your
 * app/config files
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('cardinity_client');
        $rootNode = $treeBuilder;

        $rootNode->getRootNode()
            ->children()
                ->scalarNode('consumer_key')
                   ->isRequired()
                ->end()
                ->scalarNode('consumer_secret')
                    ->isRequired()
                ->end()
            ->end();


        return $treeBuilder;
    }
}
