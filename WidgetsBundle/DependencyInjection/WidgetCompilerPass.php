<?php

namespace Trinity\WidgetsBundle\DependencyInjection;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;


/**
 * Class WidgetCompilerPass
 * @package Trinity\WidgetsBundle\DependencyInjection
 */
class WidgetCompilerPass implements CompilerPassInterface
{

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $definition = ($container->findDefinition('trinity.widgets.manager'));
        $tagServices = $container->findTaggedServiceIds('trinity.widget');

        foreach ($tagServices as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition->addMethodCall(
                    'addWidget',
                    [
                        new Reference($id),
                        $attributes["alias"],
                    ]
                );
            }
        }
    }
}