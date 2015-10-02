<?php


namespace Trinity\WidgetsBundle\Widget;


/**
 * Class ChartWidget
 * @package Trinity\WidgetsBundle\Widget
 */
abstract class ChartWidget extends AbstractWidget
{
    /** @var string */
    protected $template = "TrinityWidgetsBundle::widget_chart_layout.html.twig";

    /** @var string */
    protected $chartType = "BarChart";


    /**
     * @param  array $attributes
     * @return array
     */
    function buildWidget(array $attributes = [])
    {
        $context = $attributes;
        $context['chartType'] = $this->chartType;

        $bch = $this->buildChart($attributes);
        $context = array_merge($context, $bch);

        return $context;
    }


    /**
     * @param array $attributes
     * @return array
     */
    abstract function buildChart(array $attributes = []);

}


//@todo - check if context is array