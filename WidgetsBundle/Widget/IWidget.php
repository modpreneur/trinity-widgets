<?php
/*
 * This file is part of the Trinity project.
 */

namespace Trinity\WidgetsBundle\Widget;


/**
 * Interface IWidget
 * @package Trinity\WidgetsBundle\Widget
 */
interface IWidget
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