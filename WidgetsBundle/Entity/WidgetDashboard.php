<?php

namespace Trinity\WidgetsBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;


/**
 * Class WidgetsStorage
 * @package Trinity\WidgetsBundle\Entity
 *
 * @ORM\Table(name="base_widget_dashboard")
 * @ORM\Entity()
 *
 */
class BaseWidgetsDashboard
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
     *
     * [widgetA, widgetB] -> array with widget name
     *
     * @ORM\Column(type="array")
     */
    private $widgets;


    /**
     * @var DateTime
     * @ORM\Column(type="datetime")
     */
    private $createdAt;


    /**
     * @var DateTime
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;


    /**
     * @ORM\PrePersist()
     */
    public function prePersist()
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTime();
        }

        $this->updatedAt = new \DateTime();
    }


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
    public function getWidgets()
    {
        return $this->widgets;
    }


    /**
     * @param array $widgets
     */
    public function setWidgets($widgets)
    {
        $this->widgets = $widgets;
    }


    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }


    /**
     * @param DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }


    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }


    /**
     * @param mixed $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }


    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

}