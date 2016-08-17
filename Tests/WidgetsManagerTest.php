<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\Bundle\WidgetsBundle\Tests;

use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Bundle\FrameworkBundle\Templating\GlobalVariables;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Trinity;
use Twig_Environment;
use Twig_Extension_Debug;
use Twig_Extension_StringLoader;
use Twig_Loader_Filesystem;

/**
 * Class WidgetsManagerTest.
 */
class WidgetsManagerTest extends BaseTest
{
    /**
     * @test
     *
     * @throws \Trinity\Bundle\WidgetsBundle\Exception\WidgetException
     */
    public function testManager()
    {
        $manager = $this->getManager();
        static::assertNotNull($manager->createWidget('testWidget'));
    }


    /**
     * @return Trinity\Bundle\WidgetsBundle\Widget\WidgetManager
     */
    public function getManager()
    {
        return $this->getContainer()->get('trinity.widgets.manager');
    }


    /**
     * @test
     */
    public function testRenderWidget()
    {
        $twig = $this->getTwig();
//        $manager = $this->getManager();

        $template = $twig->loadTemplate('index.html.twig');
        $result   = $template->render(['app' => 1]);
        
        static::assertContains('Non define', $result);
        static::assertContains('Test widget', $result);
    }


    /**
     * @return Twig_Environment
     */
    public function getTwig()
    {
        $loader = new Twig_Loader_Filesystem([__DIR__.'/templates', __DIR__.'/../Resources/views/']);

        $twig = new Twig_Environment($loader, ['cache' => false, 'debug' => true]);
        $twig->addExtension(new Twig_Extension_Debug());
        $twig->addExtension(new Twig_Extension_StringLoader());
        $twig->addExtension(new RoutingExtension(new class () implements UrlGeneratorInterface{
            
            public function setContext(RequestContext $context)
            {
                // TODO: Implement setContext() method.
            }


            public function getContext()
            {
                // TODO: Implement getContext() method.
            }

            public function generate($name, $parameters = [], $referenceType = self::ABSOLUTE_PATH)
            {
                return '';
            }
        }));
        $twig->addExtension($this->getContainer()->get('trinity.widgets.extension'));

        $g = new GlobalVariables($this->getContainer());
        $twig->addGlobal('app', $g);

        return $twig;
    }
}
