<?php
/*
 * This file is part of the Trinity project.
 */

namespace Trinity\WidgetsBundle\Widget;

use Trinity\WidgetsBundle\Widget\Table\Column;
use Trinity\WidgetsBundle\Widget\Table\TableBody;
use Trinity\WidgetsBundle\Widget\Table\TableHeader;


/**
 * Class AbstractTableWidget
 * @package Trinity\WidgetsBundle\Widget
 */
abstract class TableWidget extends AbstractWidget
{
    /** @var  TableHeader */
    protected $tableHeader;

    /** @var  TableBody */
    protected $tableBody;


    protected $template = "TrinityWidgetsBundle::widget_table_layout.html.twig";


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


    public function buildWidget(array $attributes = [])
    {
        $this->tableHeader = new TableHeader();
        $this->tableBody = new TableBody();

        $context = $this->buildTable($attributes);
        $headerColumns = $this->tableHeader->getColumns();
        $body = $this->tableBody->getData();

        $context["header"] = $headerColumns;
        $context["body"] = $body;

        return $context;
    }


    /**
     * @param array $attributes
     * @return mixed
     */
    public abstract function buildTable(array $attributes = []);

}