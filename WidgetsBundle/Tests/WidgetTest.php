<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\WidgetsBundle\Tests;

use Trinity\WidgetsBundle\Tests\Widgets\TestWidget;
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
        $widget = new TestWidget(new WidgetType('widget-id', 'AbstractWidget name'));

        $this->assertEquals('testWidget', $widget->getName());

        $widget->setAttributes(['title' => 'Widget title']);
        $this->assertEquals(['title' => 'Widget title'], $widget->getAttributes());

        //$widget->setTemplate('template.html.twig');
        $this->assertEquals('widget.html.twig', $widget->getTemplate());

        $this->assertEquals('AbstractWidget title', $widget->getAttribute('title'));
        $this->assertNull($widget->getAttribute('title-no-exists'));

        $type = new WidgetType('a', 'A');
        $widget->setType($type);
        $this->assertEquals($type, $widget->getType());

        unset($widget['title']);
        $this->assertNull($widget['title']);
    }
}
