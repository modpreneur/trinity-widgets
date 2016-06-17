<?php
/*
 * This file is part of the Trinity project.
 */

namespace Trinity\Bundle\WidgetsBundle\Widget;

use Trinity\Bundle\WidgetsBundle\Widget\Table\Column;
use Trinity\Bundle\WidgetsBundle\Widget\Table\TableBody;
use Trinity\Bundle\WidgetsBundle\Widget\Table\TableHeader;


/**
 * Class AbstractTableWidget
 * @package Trinity\Bundle\WidgetsBundle\Widget
 */
abstract class TableWidget extends AbstractWidget
{
    /** @var  TableHeader */
    protected $tableHeader;

    /** @var  TableBody */
    protected $tableBody;

    /** @var string */
    protected $template = 'WidgetsBundle::widget_table_layout.html.twig';


    /**
     * @param string $id
     * @param string $title
     * @param string|null $width
     * @return Column
     */
    public function addColumn($id, $title, $width = null)
    {
        $column = new Column();
        $column->setId($id);
        $column->setTitle($title);
        $column->setWidth($width);
        $this->tableHeader->addColumn($column);

        return $column;
    }


    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->tableBody->setData($data);
    }

    /**
     * @param array $attributes
     * @return mixed
     */
    public function buildWidget(array $attributes = [])
    {
        $this->tableHeader = new TableHeader();
        $this->tableBody = new TableBody();

        $context = $this->buildTable($attributes);
        $headerColumns = $this->tableHeader->getColumns();
        $body = $this->tableBody->getData();

        $context['header'] = $headerColumns;
        $context['body']   = $body;

        return $context;
    }


    /**
     * @param array $attributes
     * @return mixed
     */
    public abstract function buildTable(array $attributes = []);

}