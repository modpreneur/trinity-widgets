<?php
/**
 * This file is part of Trinity package.
 */

namespace Trinity\Bundle\WidgetsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;


/**
 * Class WidgetCompilerPass
 * @package Trinity\Bundle\WidgetsBundle\DependencyInjection
 */
class WidgetCompilerPass implements CompilerPassInterface
{

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $definition  = $container->findDefinition('trinity.widgets.manager');
        $tagServices = $container->findTaggedServiceIds('trinity.widget');

        foreach ($tagServices as $id => $tags) {
            foreach ($tags as $attributes) {

                $alias = null;
                if(array_key_exists('alias', $attributes)){
                    $alias = $attributes["alias"];
                }

                $definition->addMethodCall(
                    'addWidget',
                    [
                        new Reference($id),
                        $alias,
                    ]
                );
            }
        }
    }
}