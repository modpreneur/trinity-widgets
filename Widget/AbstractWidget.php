<?php
/*
 * This file is part of the Trinity project.
 */

namespace Trinity\Bundle\WidgetsBundle\Widget;

/**
 * Class AbstractWidget.
 */
abstract class AbstractWidget extends AbstractArrayWidget implements WidgetInterface
{

    /** @var string */
    protected $name;

    /** @var string */
    protected $template;

    /** @var int */
    protected $size = WidgetSizes::NORMAL;

    /** @var  string */
    protected $title;

    /** @var  string dashboard, settings etc. */
    protected $type;

    /** @var  WidgetManager */
    protected $manager;

    /** @var string */
    protected $routeName;

    protected $gridParameters;


    /**
     * @return WidgetManager
     */
    public function getManager()
    {
        return $this->manager;
    }


    /**
     * @param WidgetManager $manager
     */
    public function setManager($manager)
    {
        $this->manager = $manager;
    }


    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }


    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }


    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }


    /**
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }


    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }


    /**
     * @param int $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }


    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
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


    /**
     * @return string
     */
    public function getRouteName()
    {
        return $this->routeName;
    }


    /**
     * @param $routeName
     */
    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;
    }


    /**
     * @return mixed
     */
    public function getGridParameters()
    {
        return $this->gridParameters;
    }


    /**
     * @param $gridParameters
     */
    public function setGridParameters($gridParameters)
    {
        $this->gridParameters = $gridParameters;
    }


    /**
     * @param array $attributes
     * @return array
     */
    abstract public function buildWidget(array $attributes = []);
}
