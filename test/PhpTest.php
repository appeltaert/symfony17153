<?php
 
 /**
 * (c) Bureau Pieper <piet@bureaupieper.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// * canBeEnabled DocBlock for reference
// *
// * Adds an "enabled" boolean to enable the current section.
// *
// * By default, the section is disabled. If any configuration is specified then
// * the node will be automatically enabled:
// *
// * enableableArrayNode: {enabled: true, ...}   # The config is enabled & default values get overridden
// * enableableArrayNode: ~                      # The config is enabled & use the default values
// * enableableArrayNode: true                   # The config is enabled & use the default values
// * enableableArrayNode: {other: value, ...}    # The config is enabled & default values get overridden
// * enableableArrayNode: {enabled: false, ...}  # The config is disabled
// * enableableArrayNode: false                  # The config is disabled

/**
 * Class PhpTest
 */
class PhpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Just passing false, config is disabled? No it isn't, it still tries to add defaults, and dies
     * on the first cannotBeEmpty for example.
     *
     * enableableArrayNode: false                  # The config is disabled
     */
    function testConfigIsDisabled()
    {
        $processedTree = $this->process(['mail' => false]);

        $this->assertArraySubset(['mail' => ['enabled' => false]], $processedTree);
    }

    /**
     * Different notation, same problem. The config is not disabled, it's still validated.
     *
     * enableableArrayNode: {enabled: false, ...}  # The config is disabled
     */
    function testConfigIsDisabledLongNotation()
    {
        $res = $this->process(['mail' => ['enabled' => false]]);

        $this->assertArraySubset(['mail' => ['enabled' => false]], $res);
    }

    /**
     * All the enabling ones work accordingly
     *
     * enableableArrayNode: ~                      # The config is enabled & use the default values
     *
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    function testPassingNull()
    {
        $this->process(['mail' => null]);
    }

    /**
     * All the enabling ones work accordingly
     *
     * enableableArrayNode: {enabled: true, ...}   # The config is enabled & default values get overridden
     *
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    function testPassingTrue()
    {
        $this->process(['mail' => true]);
    }

    /**
     * All the enabling ones work accordingly
     *
     * enableableArrayNode: true                   # The config is enabled & use the default values
     *
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    function testPassingArrayTrue()
    {
        $this->process(['mail' => ['enabled' => true]]);
    }

    /**
     * @param array $config
     */
    function process(array $config)
    {
        $treeBuilder = new \Symfony\Component\Config\Definition\Builder\TreeBuilder();
        $rootNode = $treeBuilder->root('root', 'array');
        $root = $rootNode->children();
        $mail = $root->arrayNode('mail')
            ->canBeEnabled()
            ->addDefaultsIfNotSet()
            ->children();
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

        $processor = new \Symfony\Component\Config\Definition\Processor();

        return $processor->process($treeBuilder->buildTree(), ['root' => $config]);
    }
}
