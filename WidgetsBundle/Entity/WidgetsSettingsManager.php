<?php
/**
 * This file is part of Trinity package.
 */

namespace Trinity\WidgetsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Trinity\WidgetsBundle\Exception\WidgetException;


/**
 * Class WidgetsSettingsManager
 * @package Trinity\WidgetsBundle\Entity
 *
 * @ORM\Entity()
 */
class WidgetsSettingsManager
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var array
     * @ORM\Column(type="array")
     */
    private $widgetsSettings;


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @param string $widget widget name
     * @return array
     * @throws WidgetException
     */
    public function getWidgetSettings($widget)
    {
        if (is_string($widget)) {
            if (array_key_exists($widget, $this->widgetsSettings)) {
                return $this->widgetsSettings[$widget];
            }
        } else {
            throw new WidgetException('Widget must be string (widget name).');
        }

        /** default settings */
        return ['order' => 0];
    }


    /**
     * @param $widget
     * @param $settings
     * @throws WidgetException
     */
    public function setWidgetSettings($widget, $settings)
    {
        if (is_string($widget)) {
            $this->widgetsSettings[$widget] = $settings;
        } else {
            throw new WidgetException('Widget must be string (widget name).');
        }
    }

}