<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\WidgetsBundle\Widget;

/**
 * Class AbstractWidget.
 */
abstract class AbstractWidget implements \ArrayAccess
{

    /** @var string */
    protected $template;

    /** @var int */
    protected $size = WidgetSizes::Small;

    /** @var array */
    protected $attributes = [];


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


    /** @inheritdoc */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->getAttributes());
    }


    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }


    /**
     * @param array $attributes
     *
     * @return $this
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }


    /** @inheritdoc */
    public function offsetGet($offset)
    {
        return $this->getAttribute($offset);
    }


    /**
     * @param $attribute
     *
     * @return string|null
     */
    public function getAttribute($attribute)
    {
        if (isset($this->attributes[$attribute])) {
            return $this->attributes[$attribute];
        } else {
            return null;
        }
    }


    /** @inheritdoc */
    public function offsetSet($offset, $value)
    {
        $this->addAttributes($offset, $value);
    }


    /** @inheritdoc */
    public function addAttributes($name, $attr)
    {
        $this->attributes[$name] = $attr;

        return $this;
    }


    /** @inheritdoc */
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
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


    /** @return string */
    abstract function getName();


    /**
     * @param array $attributes
     * @return array
     */
    abstract function buildWidget(array $attributes = []);
}
