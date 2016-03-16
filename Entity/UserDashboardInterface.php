<?php
/**
 * This file is part of Trinity package.
 */


namespace Trinity\Bundle\WidgetsBundle\Entity;


/**
 * Interface UserDashboardInterface
 * @package Trinity\Bundle\WidgetsBundle\Entity
 */
interface UserDashboardInterface
{

    /**
     * @return WidgetsDashboard
     */
    public function getWidgetsDashboard();


    /**
     * @param WidgetsDashboard $widgetsDashboard
     */
    public function setWidgetsDashboard($widgetsDashboard);


    /**
     * @param WidgetsSettingsManager $widgetsSettingsManager
     * @return void
     */
    public function setWidgetsSettingsManager(WidgetsSettingsManager $widgetsSettingsManager);


    /**
     * @return WidgetsSettingsManager
     */
    public function getWidgetsSettingsManager();

}