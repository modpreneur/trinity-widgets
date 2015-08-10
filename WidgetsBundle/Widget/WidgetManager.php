<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\WidgetsBundle\Widget;

use Dmishh\Bundle\SettingsBundle\Manager\SettingsManager;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Trinity\WidgetsBundle\Event\WidgetEvent;
use Trinity\WidgetsBundle\Event\WidgetsEvents;
use Trinity\WidgetsBundle\Exception\WidgetException;



/**
 * Class WidgetManager.
 */
class WidgetManager
{
    /** @var Widget[] */
    private $widgets = [];

    /** @var WidgetType[] */
    private $widgetsTypes = [];

    /** @var bool */
    private $init = false;

    /** @var  TraceableEventDispatcher */
    private $eventDispatcher;

    /** @var  SettingsManager */
    private $settingsManager;



    /**
     * @param EventDispatcher $eventDispatcher
     * @param $settingsManager
     */
    public function __construct($eventDispatcher, $settingsManager = null)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->settingsManager = $settingsManager;

        if (!$this->init) {
            $this->eventDispatcher->dispatch(WidgetsEvents::WIDGET_TYPE_INIT, new WidgetEvent($this));
            $this->eventDispatcher->dispatch(WidgetsEvents::WIDGET_INIT, new WidgetEvent($this));
        }

        $this->init = true;
    }



    /**
     * @param string $id
     * @param WidgetType|string $type
     * @param string $name
     * @param string $template
     * @param array $attributes
     * @param bool $autoAdd
     *
     * @return Widget
     *
     * @throws WidgetException
     */
    public function createWidget($id, $type, $name = '', $template = '', $attributes = [], $autoAdd = true)
    {
        if (is_string($type)) {
            $type = $this->getType($type);
        }

        $widget = new Widget($id, $type, $name, $template, $attributes);
        if ($autoAdd) {
            $this->addWidget($widget);
        }

        return $widget;
    }



    /**
     * @param string $type
     *
     * @return WidgetType
     *
     * @throws WidgetException
     */
    public function getType($type)
    {
        if ($this->isWidgetTypeExists($type)) {
            return $this->widgetsTypes[$type];
        } else {
            throw new WidgetException("Widget type '$type' doesn't exists.");
        }
    }



    /**
     * @param string $type
     *
     * @return bool
     */
    public function isWidgetTypeExists($type)
    {
        if (array_key_exists($type, $this->widgetsTypes)) {
            return true;
        } else {
            return false;
        }
    }



    /**
     * @param Widget $widget
     * @param null $callback
     *
     * @throws WidgetException
     */
    public function addWidget(Widget $widget, $callback = null)
    {
        if (!array_key_exists($widget->getId(), $this->widgets)) {
            $this->widgets[$widget->getId()] = $widget;
        } else {
            throw new WidgetException('This widget is already exists.');
        }

        if ($callback && is_callable($callback)) {
            call_user_func($callback, $widget);
        }
    }



    /**
     * @param string $widgetId
     *
     * @return Widget
     *
     * @throws WidgetException
     */
    public function getWidget($widgetId)
    {
        if (array_key_exists($widgetId, $this->widgets)) {
            return $this->widgets[$widgetId];
        }
        throw new WidgetException('This widget not exists.');
    }



    /**
     * @param WidgetType $type
     *
     * @throws WidgetException
     */
    public function addType(WidgetType $type)
    {
        if (!$this->isWidgetTypeExists($type->getId())) {
            $this->widgetsTypes[$type->getId()] = $type;
        } else {
            $id = $type->getId();
            throw new WidgetException("Widget type '$id' already exists.");
        }
    }



    /**
     * @param string $id
     * @param string $name
     * @param bool $autoAdd
     *
     * @return WidgetType
     */
    public function createType($id, $name, $autoAdd = true)
    {
        $type = new WidgetType($id, $name);
        if ($autoAdd && !$this->isWidgetTypeExists($type->getId())) {
            $this->widgetsTypes[$id] = $type;
        }

        return $type;
    }



    /**
     * @param string $typeId
     *
     * @return string[]
     */
    public function getWidgetsIdsByTypeId($typeId)
    {
        $widgetsIds = [];
        foreach ($this->widgets as $widget) {
            if ($widget->getType()->getId() == $typeId) {
                $widgetsIds[] = $widget->getId();
            }
        }

        return $widgetsIds;
    }



    /**
     * @param string $categoryName
     *
     * @return array
     */
    public function getTypesByCategory($categoryName)
    {
        $types = $this->widgetsTypes;
        $types = array_filter(
            $types,
            function (WidgetType $widgetType) use ($categoryName) {
                if (in_array($categoryName, $widgetType->getCategories())) {
                    return $widgetType;
                }

                return [];
            }
        );

        usort(
            $types,
            function (WidgetType $a, WidgetType $b) use ($categoryName) {
                return strcmp($a->getCategoryOrder($categoryName), $b->getCategoryOrder($categoryName));
            }
        );

        return $types;
    }
}
