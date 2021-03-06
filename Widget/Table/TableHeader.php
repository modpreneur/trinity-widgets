<?php

namespace Trinity\Bundle\WidgetsBundle\Widget\Table;


/**
 * Class TableHeader
 * @package Trinity\Bundle\WidgetsBundle\Widget\Table
 */
class TableHeader
{
    /** @var Column[] */
    private $columns = [];


    public function addColumn(Column $column)
    {
        $this->columns[] = $column;
    }


    /**
     * @return Column[]
     */
    public function getColumns()
    {
        return $this->columns;
    }


    /**
     * @param Column[] $columns
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;
    }

}