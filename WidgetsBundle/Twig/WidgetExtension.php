<?php

namespace Trinity\WidgetsBundle\Twig;

use Symfony\Component\HttpFoundation\Request;
use Trinity\WidgetsBundle\Exception\WidgetException;
use Trinity\WidgetsBundle\Widget\WidgetManager;
use Twig_Environment;



class WidgetExtension extends \Twig_Extension
{
    /** @var  WidgetManager */
    private $widgetManager;

    /** @var  Request */
    private $request;



    /**
     * @param $container
     * @param $widgetManager
     */
    public function __construct($container, $widgetManager)
    {
        $this->widgetManager = $widgetManager;
        $this->request = null;
    }



    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'renderWidget',
                array($this, 'renderWidget'),
                array('is_safe' => array('html'), 'needs_environment' => true)
            ),
            new \Twig_SimpleFunction(
                'renderWidgets',
                array($this, 'renderWidgets'),
                array('is_safe' => array('html'), 'needs_environment' => true)
            ),
            new \Twig_SimpleFunction('get_widgets', array($this, 'getWidgets')),
        );
    }



    /**
     * @param Twig_Environment $env
     * @param string[] $widgets
     *
     * @return string
     *
     * @throws WidgetException
     */
    public function renderWidgets(Twig_Environment $env, array $widgets)
    {
        $result = '';
        foreach ($widgets as $widget) {
            if (!array_key_exists('id', $widget)) {
                throw new WidgetException(
                    "Define widgets array: [ 'id'=> 'widget-id', 'params' => ['key' => 'value'] ]"
                );
            }

            $params = [];
            if (array_key_exists('params', $widget)) {
                $params = $widget['params'];
            }
            $result .= "\n".$this->renderWidget($env, $widget['id'], $params);
        }

        return $result;
    }



    /**
     * @param Twig_Environment $env
     * @param string $widgetId
     * @param string[] $options
     *
     * @return string
     *
     * @throws WidgetException
     */
    public function renderWidget(Twig_Environment $env, $widgetId, $options = [])
    {
        $widget = $this->widgetManager->getWidget($widgetId);
        $template = $env->loadTemplate($widget->getTemplate());

        return $template->render(
            [
                'widget' => $widget,
                'title' => $widget->getName(), // shortcut
                'widget-id' => $widgetId,
                'options' => $options,
            ]
        );
    }



    /**
     * @param string|null $typeId
     *
     * @return string[]
     */
    public function getWidgetsByTypeId($typeId = null)
    {
        $widgets = $this->widgetManager->getWidgetsIdsByTypeId($typeId);

        return $widgets;
    }



    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'widget_extension';
    }
}
