<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\WidgetsBundle\Tests;

use Trinity;
use Trinity\WidgetsBundle\Widget\Widget;
use Trinity\WidgetsBundle\Widget\WidgetType;
use Twig_Environment;
use Twig_Extension_Debug;
use Twig_Extension_StringLoader;
use Twig_Loader_Filesystem;



/**
 * Class WidgetsTest.
 */
class WidgetsTest extends BaseTest
{
    /**
     * @test
     *
     * @throws Trinity\WidgetsBundle\Exception\WidgetException
     */
    public function testManager()
    {
        $manager = $this->getManager();
        $widget = new Trinity\WidgetsBundle\Widget\Widget('test-id', null, 'Widget name', 'widget.html.twig');
        $manager->addWidget($widget);
        $this->assertEquals($widget, $manager->getWidget('test-id'));
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
     *
     * @throws Trinity\WidgetsBundle\Exception\WidgetException
     *
     * @expectedException Trinity\WidgetsBundle\Exception\WidgetException
     * @expectedExceptionMessage This widget is already exists.
     */
    public function testAddWithError()
    {
        $manager = $this->getManager();
        $widget = new Trinity\WidgetsBundle\Widget\Widget('test-id', null, 'Widget name', 'widget.html.twig');

        $manager->addWidget($widget);
        $manager->addWidget($widget);
    }



    /**
     * @test
     * @expectedException Trinity\WidgetsBundle\Exception\WidgetException
     * @expectedExceptionMessage Widget type 'new-type' doesn't exists.
     */
    public function testCreateWidgetWithNoExistsType()
    {
        $manager = $this->getManager();
        $manager->createWidget('test-id', 'new-type', 'Widget name', 'widget.html.twig');
    }



    /**
     * @test
     */
    public function testAddType()
    {
        $manager = $this->getManager();
        $manager->createType('type-id', 'Type name');

        $this->assertTrue($manager->isWidgetTypeExists('type-id'));

        $type = $manager->createType('type-id-none', 'Type id none', false);
        $manager->addType($type);

        $this->assertTrue($manager->isWidgetTypeExists('type-id-none'));
    }



    /**
     * @test
     */
    public function testGetType()
    {
        $manager = $this->getManager();
        $type = $manager->createType('type-id', 'Type name');

        $rType = $manager->getType('type-id');

        $this->assertEquals($type, $rType);
    }



    /**
     * @expectedException Trinity\WidgetsBundle\Exception\WidgetException
     * @expectedExceptionMessage Widget type 'type-id' already exists.
     */
    public function testAddTypeWithError()
    {
        $manager = $this->getManager();
        $type = $manager->createType('type-id', 'Type name');
        $manager->addType($type);
    }



    /**
     * @test
     * @expectedException Trinity\WidgetsBundle\Exception\WidgetException
     * @expectedExceptionMessage Widget type 'no-exists' doesn't exists.
     */
    public function testNoExistsType()
    {
        $manager = $this->getManager();
        $manager->getType('no-exists');
    }



    /**
     * @test
     *
     * @expectedException Trinity\WidgetsBundle\Exception\WidgetException
     * @expectedExceptionMessage This widget not exists.
     */
    public function testGetWithError()
    {
        $manager = $this->getManager();
        $manager->getWidget('no-exists-id');
    }



    /**
     * @test
     */
    public function testCreateWidget()
    {
        $manager = $this->getManager();
        $type = $manager->createType('type', 'Type name');

        $widget = $manager->createWidget('widget-id', $type, 'Widget type', 'widget.html.twig');
        $this->assertInstanceOf('\\Trinity\\WidgetsBundle\\Widget\\Widget', $widget);

        $widget = $manager->createWidget('widget-id-2', 'type', 'Widget type', 'widget.html.twig');
        $this->assertInstanceOf('\\Trinity\\WidgetsBundle\\Widget\\Widget', $widget);

        $this->assertInstanceOf('\\Trinity\\WidgetsBundle\\Widget\\WidgetType', $widget->getType());
    }



    public function testGetWidgetsIdsByType()
    {
        $manager = $this->getManager();
        $type = $manager->createType('type', 'Type name');
        $manager->createWidget('widget-id', $type, 'Widget type', 'widget.html.twig');

        $this->assertEquals(['widget-id'], $manager->getWidgetsIdsByTypeId('type'));
    }



    /**
     * @test
     */
    public function testRenderWidget()
    {
        $twig = $this->getTwig();

        $widget = new Widget('test-id', null, 'Widget name', 'widget.html.twig');
        $manager = $this->getManager();

        $manager->addWidget(
            $widget,
            function (Widget $widget) {
                $widget['title'] = 'Widget title';
            }
        );

        $template = $twig->loadTemplate('index.html.twig');

        $result = $template->render([]);

        $this->assertContains('Non define', $result);
        $this->assertContains('Widget title', $result);
    }



    /**
     * @return Twig_Environment
     */
    public function getTwig()
    {
        $loader = new Twig_Loader_Filesystem(__DIR__.'/templates');
        $c = $this->getContainer();

        $twig = new Twig_Environment($loader, array('cache' => false, 'debug' => true));
        $twig->addExtension(new Twig_Extension_Debug());
        $twig->addExtension(new Twig_Extension_StringLoader());
        $twig->addExtension($c->get('trinity.widgets.extension'));

        return $twig;
    }



    /**
     * @test
     */
    public function testGetTypeByCategory()
    {
        $manager = $this->getManager();
        $type = $type = $manager->createType('type', 'Category type');
        $type->addCategory('Category A', 1)->addCategory('Category B', 2);

        $typeB = $manager->createType('typeB', 'Category type B');
        $typeB->addCategory('Category A', 3);

        $this->assertEquals([$type, $typeB], $manager->getTypesByCategory('Category A'));
        $this->assertEquals([], $manager->getTypesByCategory('Category AA'));
    }



    /**
     * @test
     */
    public function testWidgetsByTypeId()
    {
        $manager = $this->getManager();
        $type = new WidgetType('type', 'Type name');

        $w1 = $manager->createWidget('widgetID-1', $type);
        $w2 = $manager->createWidget('widgetID-2', $type);

        $this->assertEquals([$w1->getId(), $w2->getId()], $manager->getWidgetsIdsByTypeId('type'));
    }
}
