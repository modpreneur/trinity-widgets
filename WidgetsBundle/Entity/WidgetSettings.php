<?php

namespace Trinity\WidgetsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Trinity\FrameworkBundle\Entity\BaseUser;


/**
 * Class WidgetSetting
 * @package Trinity\WidgetsBundle\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="widget_settings")
 */
class WidgetSettings
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
     * @var string
     * @ORM\Column(type="string")
     */
    private $name;


    /**
     * @var array
     * @ORM\Column(type="array")
     */
    private $settings;


    /**
     * @var BaseUser
     */
    private $user;


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }


    /**
     * @param array $settings
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
    }


    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

}