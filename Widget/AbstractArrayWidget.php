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


    /**
     * @param mixed $offset
     * @return mixed
     */
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


    /**
     * @param mixed $offset
     * @return null|string
     */
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


    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }


    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->addAttributes($offset, $value);
    }


    /**
     * @param $name
     * @param $attr
     * @return $this
     */
    public function addAttributes($name, $attr)
    {
        $this->attributes[$name] = $attr;

        return $this;
    }
}