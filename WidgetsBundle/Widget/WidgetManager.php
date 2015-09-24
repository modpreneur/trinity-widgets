<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\WidgetsBundle\Widget;

use Trinity\WidgetsBundle\Exception\WidgetException;
use Trinity\WidgetsBundle\Tests\Widgets\TestWidget;


/**
 * Class WidgetManager.
 */
class WidgetManager
{
    /** @var AbstractWidget[] */
    private $widgets = [];


    /**
     * @param string $name widget name
     * @param bool $clone -> new instance of widget
     * @return TestWidget
     * @throws WidgetException
     */
    public function createWidget($name, $clone = true)
    {
        /** @var AbstractWidget $widget */
        $widget = $clone ? clone $this->getWidget($name) : $this->getWidget($name);

        return $widget;
    }


    /**
     * @param string $name
     * @return AbstractWidget
     */
    private function getWidget($name)
    {
        return $this->widgets[$name];
    }


    /**
     * @param AbstractWidget $widget
     * @param callback|null $callback
     *
     * @throws WidgetException
     */
    public function addWidget(AbstractWidget $widget, $callback = null)
    {
        if (!array_key_exists($widget->getName(), $this->widgets)) {
            $this->widgets[$widget->getName()] = $widget;
        } else {
            throw new WidgetException('This widget is already registered.');
        }

        if ($callback && is_callable($callback)) {
            call_user_func($callback, $widget);
        }
    }

}
