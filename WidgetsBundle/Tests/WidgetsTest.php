<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\WidgetsBundle\Tests;

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
     * @throws \Trinity\WidgetsBundle\Exception\WidgetException
     */
    public function testManager()
    {
        $manager = $this->getManager();
        $this->assertNotNull($manager->getWidget('testWidget'));
    }


    /**
     * @return Trinity\WidgetsBundle\Widget\WidgetManager
     */
    public function getManager()
    {
        return $this->getContainer()->get('trinity.widgets_manager');
    }


    /**
     * @test
     */
    public function testRenderWidget()
    {
        $twig = $this->getTwig();
        $manager = $this->getManager();

        $template = $twig->loadTemplate('index.html.twig');
        $result = $template->render([]);
        $this->assertContains('Non define', $result);
        $this->assertContains('Test widget ', $result);
    }


    /**
     * @return Twig_Environment
     */
    public function getTwig()
    {
        $loader = new Twig_Loader_Filesystem([__DIR__.'/templates', __DIR__.'/../Resources/views/']);
        $c = $this->getContainer();
        $twig = new Twig_Environment($loader, array('cache' => false, 'debug' => true));
        $twig->addExtension(new Twig_Extension_Debug());
        $twig->addExtension(new Twig_Extension_StringLoader());
        $twig->addExtension($c->get('trinity.widgets.extension'));
        return $twig;
    }


}
