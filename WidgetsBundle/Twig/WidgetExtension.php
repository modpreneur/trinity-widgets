<?php

namespace Trinity\WidgetsBundle\Twig;

use Nette\Utils\Strings;
use ReflectionObject;
use Symfony\Component\HttpFoundation\Request;
use Trinity\WidgetsBundle\Entity\WidgetsDashboard;
use Trinity\WidgetsBundle\Exception\WidgetException;
use Trinity\WidgetsBundle\Widget\AbstractWidget;
use Trinity\WidgetsBundle\Widget\IRemovable;
use Trinity\WidgetsBundle\Widget\IResizable;
use Trinity\WidgetsBundle\Widget\WidgetManager;
use Twig_Environment;


/**
 * Class WidgetExtension
 * @package Trinity\WidgetsBundle\Twig
 */
class WidgetExtension extends \Twig_Extension
{
    /** @var  WidgetManager */
    private $widgetManager;

    /** @var  Request */
    private $request;

    /** @var  \Twig_TemplateInterface */
    private $template;


    /**
     * WidgetExtension constructor.
     * @param WidgetManager $widgetManager
     */
    public function __construct(WidgetManager $widgetManager)
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
            new \Twig_SimpleFunction('getWidgetUrl', [$this, 'getWidgetUrl'], ['is_safe' => ['html']]),

            new \Twig_SimpleFunction(
                'renderDashboard',
                array($this, 'renderDashboard'),
                array('is_safe' => array('html'), 'needs_environment' => true)
            ),
            new \Twig_SimpleFunction(
                'renderTableCell', [$this, 'renderTableCell'], ['is_safe' => array('html')]
            ),
            new \Twig_SimpleFunction(
                'widget_*', [$this, 'widget'], ['is_safe' => ['html'], 'needs_environment' => true]
            ),
        );
    }


    /**
     * @param string $section
     * @param AbstractWidget $widget
     * @return string
     */
    public function getWidgetUrl($section, AbstractWidget $widget)
    {

        $prefix = $this->widgetManager->getRouteUrl().(strpos(
                $this->widgetManager->getRouteUrl(),
                '?widget_'
            ) ? '&' : '?widget_');
        $url = '';

        switch ($section) {
            case 'remove':
                $url = $prefix.'remove='.$widget->getName();
                break;
        }

        return $url;

    }


    public function widget(Twig_Environment $env)
    {

    }


    /**
     * @param $object
     * @param $attribute
     * @return string
     * @throws \Exception
     */
    public function renderTableCell($object, $attribute)
    {
        $result = null;

        $reflection = new ReflectionObject($object);
        if (property_exists($object, $attribute)) {
            $methods = ["get", "is", "has"];
            foreach ($methods as $method) {
                if (method_exists($object, $method.ucfirst($attribute))) {
                    $method = $reflection->getMethod($method.ucfirst($attribute));
                    $result = $method->invoke($object);
                    break;
                }
            }
        } elseif (method_exists($object, $attribute)) {
            $method = $reflection->getMethod($attribute);
            $result = $method->invoke($object);
        }


        if ($result instanceof \DateTime) $result = $this->template->renderBlock(
            'widget_cell_datetime',
            ['value' => $result, 'row' => $object]
        ); elseif (is_bool($result)) {
            $result = $this->template->renderBlock('widget_cell_boolean', ['value' => $result, 'row' => $object]);
        } elseif (Strings::startsWith($result, "http") || Strings::startsWith($result, "www")) {
            $result = $this->template->renderBlock('widget_cell_link', ['value' => $result, 'row' => $object]);
        } else {
            $result = $this->template->renderBlock('widget_cell_string', ['value' => $result, 'row' => $object]);
        }

        return $result;
    }


    public function renderDashboard(Twig_Environment $env, WidgetsDashboard $dashboard)
    {
        $widgetsNames = $dashboard->getWidgets();

        /** @var \Twig_TemplateInterface $template */
        $template = $env->loadTemplate("TrinityWidgetsBundle::dashboard.html.twig");

        $context = [
            'widgets' => $widgetsNames,
        ];

        return $template->render($context);
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
     * {{ renderWidget("projects_list", {"title": "Products list"}) }}
     *
     * @param Twig_Environment $env
     * @param string $widgetName
     * @param string[] $options
     *
     * @return string
     *
     * @throws WidgetException
     */
    public function renderWidget(Twig_Environment $env, $widgetName, $options = [])
    {
        /** @var AbstractWidget $widget */
        $widget = $this->widgetManager->createWidget($widgetName);
        /** @var \Twig_TemplateInterface $template */
        $this->template = $template = $env->loadTemplate($widget->getTemplate());

        $wb = $widget->buildWidget();

        $context = [
            'name' => $widget->getName(),
            'widget' => $widget,
            'title' => $widget->getTitle(),
            'size' => $widget->getSize(),
            'resizable' => $widget instanceof IResizable,
            'removable' => $widget instanceof IRemovable,
        ];

        if ($wb && is_array($wb)) {
            $context = array_merge($context, $wb);
        }

        if ($options && is_array($options) && count($options) > 0) {
            $context = array_merge($context, $options);
        }

        return $template->render($context);
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
