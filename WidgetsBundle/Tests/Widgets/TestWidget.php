<?php
/*
 * This file is part of the Trinity project.
 */

namespace Trinity\WidgetsBundle\Tests\Widgets;

use Trinity\WidgetsBundle\Widget\AbstractWidgetInterface;
use Trinity\WidgetsBundle\Widget\RemovableInterface;


/**
 * Class TestWidget
 * @package Trinity\WidgetsBundle\Tests\Widgets
 */
class TestWidget extends AbstractWidgetInterface implements RemovableInterface
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