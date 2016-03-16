<?php

/**
 * This file is part of the Trinity project.
 */
namespace Trinity\Bundle\WidgetsBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Trinity\Bundle\WidgetsBundle\DependencyInjection\WidgetCompilerPass;


/**
 * Class TrinityWidgetsBundle.
 */
class WidgetsBundle extends Bundle
{
    /**
     * Builds the bundle.
     *
     * It is only ever called once when the cache is empty.
     *
     * This method can be overridden to register compilation passes,
     * other extensions, ...
     *
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new WidgetCompilerPass());
    }

}
