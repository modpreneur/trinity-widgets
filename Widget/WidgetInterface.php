<?php
/*
 * This file is part of the Trinity project.
 */

namespace Trinity\Bundle\WidgetsBundle\Widget;


/**
 * Interface WidgetInterface
 * @package Trinity\Bundle\WidgetsBundle\Widget
 */
interface WidgetInterface
{

    /**
     * @return string
     */
    function getName();


    /**
     * @param array $attributes
     * @return array
     */
    function buildWidget(array $attributes = []);

}