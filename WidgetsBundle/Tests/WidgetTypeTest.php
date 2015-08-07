<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\WidgetsBundle\Tests;

use Trinity\WidgetsBundle\Widget\WidgetType;



/**
 * Class WidgetTypeTest.
 */
class WidgetTypeTest extends BaseTest
{
    public function testWidgetType()
    {
        $type = new WidgetType('id', 'name');
        $type->setId('id');
        $this->assertEquals('id', $type->getId());

        $type->setName('Type name');

        $this->assertEquals('Type name', $type->getName());

        $type->addCategory('new category');

        $this->assertEquals(['new category'], $type->getCategories());

        $type->setCategories(['c']);
        $this->assertEquals(['c'], $type->getCategories());

        $this->assertEquals(0, $type->getCategoryOrder('c'));
    }
}
