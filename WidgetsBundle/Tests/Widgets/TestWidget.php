<?php
/*
 * This file is part of the Trinity project.
 */

namespace Trinity\WidgetsBundle\Tests\Widgets;

use Trinity\WidgetsBundle\Widget\AbstractWidget;
use Trinity\WidgetsBundle\Widget\IRemovable;


/**
 * Class TestWidget
 * @package Trinity\WidgetsBundle\Tests\Widgets
 */
class TestWidget extends AbstractWidget implements IRemovable
{

    /** @var string */
    protected $template = "widget.html.twig";


    function buildWidget(array $attributes = [])
    {
        $this['title'] = 'Test widget';
        $this['options'] = $attributes;
    }


    /**
     * @return void
     */
    function remove()
    {
        // TODO: Implement remove() method.
    }


    /** @return string */
    function getName()
    {
        return "testWidget";
    }
}