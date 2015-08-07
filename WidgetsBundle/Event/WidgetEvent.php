<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\WidgetsBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Trinity\WidgetsBundle\Widget\WidgetManager;



/**
 * Class WidgetEvent.
 */
class WidgetEvent extends Event
{
    /** @var  WidgetManager */
    protected $widgetManager;



    /**
     * @param WidgetManager $widgetManager
     */
    public function __construct($widgetManager)
    {
        $this->widgetManager = $widgetManager;
    }



    /**
     * @return WidgetManager
     */
    public function getWidgetManager()
    {
        return $this->widgetManager;
    }
}
