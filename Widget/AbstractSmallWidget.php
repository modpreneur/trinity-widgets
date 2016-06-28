<?php
/**
 * This file is part of Trinity package.
 */
namespace Trinity\Bundle\WidgetsBundle\Widget;

/**
 * Class AbstractSmallWidget
 * @package Trinity\Bundle\WidgetsBundle\Widget
 */
abstract class AbstractSmallWidget extends AbstractWidget
{
    protected $template = 'WidgetsBundle::widget_small_layout.html.twig';

    /** @var int */
    protected $size = WidgetSizes::SMALL;

    protected $type = 'static';

    /**
     * @param  array $attributes
     * @return array
     */
    public function buildWidget(array $attributes = [])
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
    abstract public function buildSmall(array $attributes = []);
}
