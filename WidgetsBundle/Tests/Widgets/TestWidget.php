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


    /** @inheritdoc */
    function buildWidget(array $attributes = [])
    {
        return [
            'title' => "Test widget",
        ];
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