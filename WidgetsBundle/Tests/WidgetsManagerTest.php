<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\WidgetsBundle\Tests;

use Entity\User;
use Symfony\Bundle\FrameworkBundle\Templating\GlobalVariables;
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
        $this->assertNotNull($manager->createWidget('testWidget'));
    }


    /**
     * @return Trinity\WidgetsBundle\Widget\WidgetManager
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
        $manager = $this->getManager();

        $template = $twig->loadTemplate('index.html.twig');
        $result = $template->render(['app' => 1]);
        $this->assertContains('Non define', $result);
        $this->assertContains('Test widget ', $result);
    }


    /**
     * @return Twig_Environment
     */
    public function getTwig()
    {
        $loader = new Twig_Loader_Filesystem([__DIR__.'/templates', __DIR__.'/../Resources/views/']);

        $twig = new Twig_Environment($loader, array('cache' => false, 'debug' => true));
        $twig->addExtension(new Twig_Extension_Debug());
        $twig->addExtension(new Twig_Extension_StringLoader());
        $twig->addExtension($this->getContainer()->get('trinity.widgets.extension'));

        $g = new GlobalVariables($this->getContainer());
        $twig->addGlobal('app', $g);

        return $twig;
    }
}
