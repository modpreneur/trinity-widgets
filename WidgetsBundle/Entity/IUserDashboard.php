<?php


namespace Trinity\WidgetsBundle\Entity;


/**
 * Interface IUserDashboard
 * @package Trinity\WidgetsBundle\Entity
 */
interface IUserDashboard
{

    /**
     * @return WidgetsDashboard
     */
    public function getWidgetsDashboard();


    /**
     * @param WidgetsDashboard $widgetsDashboard
     */
    public function setWidgetsDashboard($widgetsDashboard);

}