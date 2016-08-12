<?php
/**
 * This file is part of Trinity package.
 */


namespace Trinity\Bundle\WidgetsBundle\Widget;

/**
 * Class ChartWidget
 * @package Trinity\Bundle\WidgetsBundle\Widget
 */
abstract class ChartWidget extends AbstractWidget
{
    /** @var string */
    protected $template = 'WidgetsBundle::widget_chart_layout.html.twig';

    /** @var string */
    protected $chartType = 'BarChart';


    /**
     * @param  array $attributes
     * @return array
     */
    public function buildWidget(array $attributes = [])
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
    abstract public function buildChart(array $attributes = []);

    /**
     * @param string $phpFormat
     * @return string
     */
    protected function formatDateTime(string $phpFormat){
        $formats = [
            'd' => '%d',
            'D' => '%a',
            'm' => '%m',
            'Y' => '%Y',
            'j' => '%e',
            'F' => '%B',
            'n' => '%_I',
            'h' => '%I',
            'i' => '%M',
            's' => '%S',
            'A' => '%p',
            'H' => '%H',
        ];

        $d3Format = "";
        $chars = str_split($phpFormat);
        foreach ($chars as $char) {
            if (array_key_exists($char, $formats)) {
                $d3Format .= $formats[$char];
            } else {
                $d3Format .= $char;
            }
        }
        return $d3Format;
    }
}
