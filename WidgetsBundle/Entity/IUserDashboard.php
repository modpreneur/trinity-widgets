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