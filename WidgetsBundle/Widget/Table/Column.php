<?php
namespace Trinity\WidgetsBundle\Widget\Table;

/**
 * Class Column
 * @package Trinity\WidgetsBundle\Widget\Table
 */
class Column
{

    /** @var  string */
    private $id;

    /** @var string */
    private $title;

    /** @var string */
    private $width;


    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
    public function getWidth()
    {
        return $this->width;
    }


    /**
     * @param string $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

}