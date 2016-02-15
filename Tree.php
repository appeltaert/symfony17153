<?php
 
 /**
 * (c) Bureau Pieper <piet@bureaupieper.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bureaupieper\StoreeClient\Resources;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class ConfigTree
{
    static function get($platform = null)
    {
        $isSymfony = $platform == 'symfony';

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('bureaupieper_storee', 'array');
        $root = $rootNode->children();
        $root->scalarNode('endpoint')
            ->defaultValue('https://store-e.nl/api')
            ->cannotBeEmpty()
            ->validate()
                ->ifTrue(function($v) { return !preg_match('/^https?:\/\//', $v); })
                ->thenInvalid('Endpoint not http')
                ->end()
            ->end();
        $root->scalarNode('apikey')
            ->isRequired()
            ->validate()
                ->ifTrue(function($v) { return !$v; })
                ->thenInvalid('Cannot be the empty string')
            ->end()
            ->end();
        $root->scalarNode('version')
            ->isRequired()
            ->validate()
                ->ifTrue(function($v) { return !preg_match('/^[1-9][0-9]*$/', $v); })
                ->thenInvalid('Version not numeric')
                ->end()
            ->cannotBeEmpty()
            ->end();
        $root->scalarNode('platform')
            ->cannotBeEmpty()
            ->isRequired()
            ->validate()
                ->ifTrue(function($v) { return !is_string($v); })
                ->thenInvalid('Platform not a string')
                ->end()
            ->end();
        $root->scalarNode('format')
            ->defaultValue('json')
            ->validate()
                ->ifNotInArray(array('json', 'xml'))
                ->thenInvalid('Invalid format "%s"')
                ->end()
            ->cannotBeEmpty()
            ->end();

        if ($isSymfony) {
            $root->scalarNode('guzzle')
                ->defaultNull()
                ->end();
        }

        $logging = $root->arrayNode('logs')->canBeEnabled()->addDefaultsIfNotSet()->children();

            if ($isSymfony) {
                $logging->scalarNode('service')
                    ->defaultNull()
                    ->end();
            }

            $defaults = $logging->arrayNode('default_driver')->canBeDisabled()->addDefaultsIfNotSet()->children();

                $defaults->scalarNode('path')
                    ->defaultValue($isSymfony ? '%kernel.logs_dir%' : __DIR__ . '/../var/cache')
                    ->cannotBeEmpty()
                    ->end();

                $mail = $defaults->arrayNode('mail')->canBeEnabled()->addDefaultsIfNotSet()->children();
                    $mail->scalarNode('to')
                        ->cannotBeEmpty()
                        ->isRequired()
                        ->end();
                    $mail->scalarNode('subject')
                        ->cannotBeEmpty()
                        ->isRequired()
                        ->end();
                    $mail->scalarNode('from')
                        ->cannotBeEmpty()
                        ->isRequired()
                        ->end();

        $cache = $root->arrayNode('cache')->canBeEnabled()->addDefaultsIfNotSet()->children();
            $cache->integerNode('ttr')
                ->defaultValue(10 * 60)
                ->cannotBeEmpty()
                ->end();


            if ($isSymfony) {
                $cache->scalarNode('service')
                    ->defaultNull()
                    ->end();
            }

            $defaults = $cache->arrayNode('default_driver')->canBeDisabled()->addDefaultsIfNotSet()->children();
                $defaults->scalarNode('path')
                    ->defaultValue($isSymfony ? '%kernel.cache_dir%' : __DIR__ . '/../../var/cache')
                    ->cannotBeEmpty()
                    ->end();

        return $treeBuilder;
    }
}