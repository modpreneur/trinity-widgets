<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\WidgetsBundle\Widget;

use Trinity\WidgetsBundle\Event\WidgetEvent;


/**
 * Class WidgetListener.
 */
abstract class WidgetListener
{
    /**
     * @var bool
     */
    protected $initType = false;

    /**
     * @var bool
     */
    protected $initWidget = false;


    /**
     * @param WidgetEvent $we
     *
     * @return mixed
     */
    abstract public function widgetTypeInit(WidgetEvent $we);


    /**
     * @param WidgetEvent $we
     *
     * @return mixed
     */
    abstract public function widgetInit(WidgetEvent $we);
}
