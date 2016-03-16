<?php
/*
 * This file is part of the Trinity project.
 */

namespace Trinity\Bundle\WidgetsBundle\Widget;

/**
 * Class AbstractArrayWidget
 * @package Trinity\Bundle\WidgetsBundle\Widget
 */
abstract class AbstractArrayWidget implements \ArrayAccess
{
    /** @var array */
    protected $attributes = [];


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
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
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
}