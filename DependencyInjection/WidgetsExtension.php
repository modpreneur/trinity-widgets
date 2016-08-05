<?php
/*
 * This file is part of the Trinity project.
 *
 */

namespace Trinity\Bundle\WidgetsBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\Definition\Processor;
use Nette\Utils\Strings;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class WidgetsExtension extends Extension
{
    /**
     * @param array $configs
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        foreach ($config as $key => $value) {

            if(is_array($value)){
                foreach ($value as $keyk => $valueK ) {

                    if (Strings::startsWith($valueK, '@')) {
                        $valueK = new Reference($valueK);
                    }

                    $container->setParameter('widgets.' . $key . '.' . $keyk, $valueK);
                }
            }

            $container->setParameter('widgets.' . $key, $value);
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
