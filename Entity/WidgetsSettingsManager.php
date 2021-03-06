<?php
/**
 * This file is part of Trinity package.
 */

namespace Trinity\Bundle\WidgetsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Trinity\Bundle\WidgetsBundle\Exception\WidgetException;

/**
 * Class WidgetsSettingsManager
 * @package Trinity\Bundle\WidgetsBundle\Entity
 *
 * @ORM\Table(name="widgets_settings_manager")
 * @ORM\Entity(repositoryClass="WidgetsSettingsManagerRepository")
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
    private $widgetsSettings=[];


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

        if ($widget === 'globalSettings') {
            return ['hideBroken'=>false, 'hideEmpty' => false];
        }
        return ['none' => true];
    }
    
    public function clearWidgetsSettings()
    {
        $this->widgetsSettings = [];
    }


    /**
     * @param string $widget
     *
     * @throws WidgetException
     */
    public function clearWidgetSettings($widget)
    {
        if (is_string($widget)) {
            if (array_key_exists($widget, $this->widgetsSettings)) {
                unset($this->widgetsSettings[$widget]);
            }
        } else {
            throw new WidgetException('Widget must be string (widget name).');
        }

    }


    /**
     * @param $widget
     * @param $settings
     * @throws WidgetException
     */
    public function setWidgetSettings($widget, $settings)
    {
        if (is_string($widget)) {
            foreach ($settings as $newPropertyName => $newProperty) {
                if (array_key_exists($widget, $this->widgetsSettings)) {
                    if (array_key_exists($newPropertyName, $this->widgetsSettings[$widget])) {
                        foreach ($this->widgetsSettings[$widget] as $propertyName => $property) {
                            if ($propertyName === $newPropertyName) {
                                $this->widgetsSettings[$widget][$propertyName] = $newProperty;
                            }
                        }
                    } else {
                        $this->widgetsSettings[$widget][$newPropertyName] = $newProperty;
                    }
                } else {
                    $this->widgetsSettings[$widget]=$settings;
                }
            }
        } else {
            throw new WidgetException('Widget must be string (widget name).');
        }
    }
}
