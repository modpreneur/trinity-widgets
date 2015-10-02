<?php

namespace Trinity\WidgetsBundle\Widget\Table;


/**
 * Class TableHeader
 * @package Trinity\WidgetsBundle\Widget\Table
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