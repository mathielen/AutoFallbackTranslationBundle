<?php

namespace Happyr\AutoFallbackTranslationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $root = $treeBuilder->root('happyr_auto_fallback_translation');

        $root->children()
            ->scalarNode('cache_adapter')->defaultNull()->end()
            ->scalarNode('http_client')->cannotBeEmpty()->defaultValue('httplug.client')->end()
            ->scalarNode('message_factory')->cannotBeEmpty()->defaultValue('httplug.message_factory')->end()
            ->enumNode('translation_service')->values(['google', 'foobar'])->defaultValue('google')->end()
            ->booleanNode('enabled')->defaultFalse()->end()
            ->scalarNode('default_locale')->defaultValue('en')->end()
            ->arrayNode('allowed_locales')->prototype('scalar')->end()->end()
            ->scalarNode('google_key')->defaultNull()->end()
        ->end();

        return $treeBuilder;
    }
}
