<?php
/**
 * This file is part of Trinity package.
 */


namespace Trinity\WidgetsBundle\Widget;


/**
 * Class SmallWidget
 * @package Trinity\WidgetsBundle\Widget
 */
abstract class SmallWidget extends AbstractWidgetInterface
{
    /** @var string */
    protected $template = "TrinityWidgetsBundle::widget_small_layout.html.twig";

    /**
     * @param  array $attributes
     * @return array
     */
    function buildWidget(array $attributes = [])
    {
        $context = $attributes;

        $bSmall = $this->buildSmall($attributes);
        $context = array_merge($context, $bSmall);

        return $context;
    }


    /**
     * @param array $attributes
     * @return array
     */
    abstract function buildSmall(array $attributes = []);

}