<?php

namespace GaylordP\UserMediaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('user_media');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('action_success_redirect_path')
                    ->isRequired()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
