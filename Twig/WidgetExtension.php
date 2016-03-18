<?php
/**
 * This file is part of Trinity package.
 */

namespace Trinity\Bundle\WidgetsBundle\Twig;

use Nette\Utils\Strings;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Trinity\Bundle\WidgetsBundle\Entity\WidgetsDashboard;
use Trinity\Bundle\WidgetsBundle\Exception\WidgetException;
use Trinity\Bundle\WidgetsBundle\Widget\AbstractWidget;
use Trinity\Bundle\WidgetsBundle\Widget\RemovableInterface;
use Trinity\Bundle\WidgetsBundle\Widget\ResizableInterface;
use Trinity\Bundle\WidgetsBundle\Widget\WidgetManager;
use Trinity\Bundle\WidgetsBundle\Widget\WidgetSizes;
use Trinity\FrameworkBundle\Entity\BaseUser;
use Trinity\FrameworkBundle\Exception\MemberAccessException;
use Trinity\FrameworkBundle\Utils\ObjectMixin;
use Twig_Environment;


/**
 * Class WidgetExtension
 * @package Trinity\Bundle\WidgetsBundle\Twig
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
     * @var RouterInterface
     */
    private $router;
    /**
     * @var RequestStack
     */
    private $requestStack;


    /**
     * WidgetExtension constructor.
     * @param WidgetManager $widgetManager
     * @param Router $router
     * @param RequestStack $requestStack
     */
    public function __construct(WidgetManager $widgetManager, Router $router, RequestStack $requestStack)
    {
        $this->widgetManager = $widgetManager;
        $this->request = null;
        $this->router = $router;
        $this->requestStack = $requestStack;
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

            new \Twig_SimpleFunction('getSizeIcon', [$this, 'getSizeIcon'], ['is_safe' => ['html']]),

            new \Twig_SimpleFunction(
                'renderDashboard',
                array($this, 'renderDashboard'),
                array('is_safe' => array('html'), 'needs_environment' => true)
            ),
            new \Twig_SimpleFunction(
                'renderTableCell', [$this, 'renderTableCell'], ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            new \Twig_SimpleFunction(
                'widget_*', [$this, 'widget'], ['is_safe' => ['html'], 'needs_environment' => true]
            ),
        );
    }


    /**
     * @param string $section
     * @param AbstractWidget $widget
     * @param BaseUser $user
     * @return string
     */
    public function getWidgetUrl($section, AbstractWidget $widget, BaseUser $user)
    {
        $url = '';
        switch ($section) {
            case WidgetManager::ACTION_REMOVE:
                $url = $this->router->generate(
                    'remove_widget',
                    array(
                        'widgetName' => $widget->getName(),
                    )
                );
                break;
            case WidgetManager::ACTION_RESIZE:
                $size = ($widget->getSize() === WidgetSizes::Normal) ? WidgetSizes::Full : WidgetSizes::Normal;

                $url = $this->router->generate(
                    'resize_widget',
                    array(
                        'widgetName' => $widget->getName(),
                        'widgetSize' => $size,
                    )
                );

                break;
        }


        return $url;
    }


    /**
     * @param AbstractWidget $widget
     * @return string
     */
    public function getSizeIcon(AbstractWidget $widget)
    {
        $icon = ($widget->getSize(
            ) === WidgetSizes::Normal) ? '<i class="trinity trinity-plus" id="get-bigger"></i><i id="get-smaller" class="trinity trinity-minus" style="display: none"></i>' : '<i class="trinity trinity-minus" id="get-smaller"></i><i class="trinity trinity-plus" id="get-bigger" style="display: none"></i>';

        return $icon;
    }


    /**
     * @param Twig_Environment $env
     */
    public function widget(Twig_Environment $env)
    {

    }


    /**
     * @param $object
     * @param $attribute
     * @return string
     * @throws \Exception
     */
    public function renderTableCell($env, $object, $attribute, $widgetName)
    {
        $widget = $this->createWidget($widgetName, $env);

        try {
            $result = ObjectMixin::get($object, $attribute);
        } catch (MemberAccessException $ex) {
            $result = "";
        }

        if ($this->template->hasBlock('widget_table_cell_'.$attribute)) {
            $result = $this->template->renderBlock(
                'widget_table_cell_'.$attribute,
                ['value' => $result, 'row' => $object]
            );

            return $result;
        } elseif ($result instanceof \DateTime) {
            $result = $this->template->renderBlock('widget_cell_datetime', ['value' => $result, 'row' => $object]);
        } elseif (is_bool($result)) {
            $result = $this->template->renderBlock('widget_cell_boolean', ['value' => $result, 'row' => $object]);
        } elseif (Strings::startsWith($result, "http") || Strings::startsWith($result, "www")) {
            $result = $this->template->renderBlock('widget_cell_link', ['value' => $result, 'row' => $object]);
        } else {
            $result = $this->template->renderBlock('widget_cell_string', ['value' => $result, 'row' => $object]);
        }

        return $result;
    }


    /**
     * @param Twig_Environment $env
     * @param WidgetsDashboard $dashboard
     * @param BaseUser $user
     * @return string
     */
    public function renderDashboard(Twig_Environment $env, WidgetsDashboard $dashboard, BaseUser $user)
    {
        $widgetsNames = $dashboard->getWidgets();
        $allWidgets = $this->widgetManager->getDashboardWidgets();
        $staticWidgets = $this->widgetManager->getStaticWidgets();
        $staticWidgetsNames=[];
        foreach($staticWidgets as $widgetName =>$widget)
        {
            $staticWidgetsNames[]=$widgetName;
        }
        $hiddenWidgetsNames = [];
        $showedWidgetsNames = [];
        $widgetsSettingsManager = $user->getWidgetsSettingsManager();

        foreach ($allWidgets as $widgetName => $widget) {
            if (in_array($widgetName, $widgetsNames)) {
                $widgetSettings = $widgetsSettingsManager->getWidgetSettings($widgetName);
                $inOrder = $widgetSettings['inOrder'];
                $showedWidgetsNames[$inOrder] = $widgetName;

            } else {
                $hiddenWidgetsNames[] = $widgetName;
            }
        }
        ksort($showedWidgetsNames);
        /** @var \Twig_TemplateInterface $template */

        $template = $env->loadTemplate("WidgetsBundle::dashboard.html.twig");
        $form = $this->widgetManager->getForm();

        $context = [
            'showedWidgets' => $showedWidgetsNames,
            'hiddenWidgets' => $hiddenWidgetsNames,
            'staticWidgets' => $staticWidgetsNames,
            'form' => $form->createView(),
        ];
        $this->widgetManager->setUser($user);

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


    public function createWidget($widgetName, $env){
        /** @var AbstractWidget $widget */
        $widget = $this->widgetManager->createWidget($widgetName);
        /** @var \Twig_TemplateInterface $template */
        $this->template = $template = $env->loadTemplate($widget->getTemplate());

        return $widget;
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
        $widget = $this->createWidget($widgetName, $env);
        $wb     = $widget->buildWidget();

        $widgetManager = $this->widgetManager->getUser()->getWidgetsSettingsManager();

        $widgetSettings = $widgetManager->getWidgetSettings($widgetName);

        if (array_key_exists('size', $widgetSettings)) {
            $widget->setSize(intval($widgetSettings['size']));
        }
        $context = [
            'name' => $widget->getName(),
            'routeName' => $widget->getRouteName(),
            'gridParameters' => $widget->getGridParameters(),
            'widget' => $widget,
            'title' => $widget->getTitle(),
            'size' => $widget->getSize(),
            'resizable' => $widget instanceof ResizableInterface,
            'removable' => $widget instanceof RemovableInterface,
        ];
        if ($wb && is_array($wb)) {
            $context = array_merge($context, $wb);
        }

        if ($options && is_array($options) && count($options) > 0) {
            $context = array_merge($context, $options);
        }
        return $this->template->render($context);
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