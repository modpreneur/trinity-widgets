<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\WidgetsBundle\Tests;

use Trinity\WidgetsBundle\Widget\Widget;
use Trinity\WidgetsBundle\Widget\WidgetType;



/**
 * Class WidgetTest.
 */
class WidgetTest extends BaseTest
{
    /**
     * @test
     */
    public function testWidget()
    {
        $widget = new Widget('widget-id', new WidgetType('widget-id', 'Widget name'), 'Widget name');

        $widget->setName('New name');
        $this->assertEquals('New name', $widget->getName());

        $widget->setOrder(0);
        $this->assertEquals(0, $widget->getOrder());

        $widget->setAttributes(['title' => 'Widget title']);
        $this->assertEquals(['title' => 'Widget title'], $widget->getAttributes());

        $widget->setTemplate('template.html.twig');
        $this->assertEquals('template.html.twig', $widget->getTemplate());

        $this->assertEquals('Widget title', $widget->getAttribute('title'));
        $this->assertNull($widget->getAttribute('title-no-exists'));

        $type = new WidgetType('a', 'A');
        $widget->setType($type);
        $this->assertEquals($type, $widget->getType());

        unset($widget['title']);
        $this->assertNull($widget['title']);
    }
}
