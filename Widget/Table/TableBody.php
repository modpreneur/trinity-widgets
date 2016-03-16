<?php


namespace Trinity\Bundle\WidgetsBundle\Widget\Table;


/**
 * Class TableBody
 * @package Trinity\Bundle\WidgetsBundle\Widget\Table
 */
class TableBody
{

    /** @var array */
    private $data;


    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }


    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

}
