<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\WidgetsBundle\Widget;

/**
 * Class Widget.
 */
class Widget implements \ArrayAccess
{
    /** @var  string */
    private $id;

    /** @var  WidgetType */
    private $type;

    /** @var  string */
    private $name;

    /** @var  int */
    private $order;

    /** @var string */
    private $template;



    /**
     * @param string $id
     * @param WidgetType $type
     * @param string $name
     * @param string $template
     * @param array $attributes
     */
    public function __construct($id, $type, $name = '', $template = '', $attributes = array())
    {
        $this->id = $id;
        $this->type = $type;
        $this->name = $name;
        $this->template = $template;
        $this->attributes = $attributes;
    }



    /**
     * @return WidgetType
     */
    public function getType()
    {
        return $this->type;
    }



    /**
     * @param WidgetType $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
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
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }



    /**
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }



    /**
     * @param int $order
     *
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
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
     * @return string
     */
    public function getId()
    {
        return $this->id;
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
     * @since 5.0.0
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return bool true on success or false on failure.
     *              </p>
     *              <p>
     *              The return value will be casted to boolean if non-boolean was returned.
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
     * @since 5.0.0
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return mixed Can return all value types.
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
     * @since 5.0.0
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value <p>
     *                      The value to set.
     *                      </p>
     */
    public function offsetSet($offset, $value)
    {
        $this->addAttributes($offset, $value);
    }



    /**
     * @param $name
     * @param $attr
     *
     * @return $this
     */
    public function addAttributes($name, $attr)
    {
        $this->attributes[$name] = $attr;

        return $this;
    }



    /**
     * @since 5.0.0
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     */
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }
}
