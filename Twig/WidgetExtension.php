<?php
/**
 * This file is part of Trinity package.
 */

namespace Trinity\Bundle\WidgetsBundle\Twig;

use Monolog\Logger;
use Nette\Utils\Strings;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Trinity\Bundle\WidgetsBundle\Entity\UserDashboardInterface;
use Trinity\Bundle\WidgetsBundle\Entity\WidgetsDashboard;
use Trinity\Bundle\WidgetsBundle\Entity\WidgetsSettingsManager;
use Trinity\Bundle\WidgetsBundle\Exception\WidgetException;
use Trinity\Bundle\WidgetsBundle\Widget\AbstractWidget;
use Trinity\Bundle\WidgetsBundle\Widget\RemovableInterface;
use Trinity\Bundle\WidgetsBundle\Widget\ResizableInterface;
use Trinity\Bundle\WidgetsBundle\Widget\WidgetManager;
use Trinity\Bundle\WidgetsBundle\Widget\WidgetSizes;
use Trinity\Component\Utils\Utils\ObjectMixin;
use Twig_Environment;
use Twig_Extension;
use Twig_Template;
use \Symfony\Component\Cache\Adapter\AdapterInterface;

/**
 * Class WidgetExtension
 * @package Trinity\Bundle\WidgetsBundle\Twig
 */
class WidgetExtension extends Twig_Extension
{
    /** @var  WidgetManager */
    private $widgetManager;

    /** @var  Request */
    private $request;

    /** @var  Twig_Template */
    private $template;

    /** @var RouterInterface */
    private $router;

    /** @var RequestStack */
    private $requestStack;

    /** @var int */
    private $widgetLayout = 24;

    /** @var int */
    private $oddEven = 1;

    /** @var AdapterInterface */
    private $cache;

    /** @var [] */
    private $config;

    /** @var Logger */
    private $logger;

    /**
     * WidgetExtension constructor.
     *
     * @param WidgetManager $widgetManager
     * @param Router $router
     * @param RequestStack $requestStack
     * @param Logger $logger
     */
    public function __construct(
        WidgetManager $widgetManager,
        Router $router,
        RequestStack $requestStack,
        Logger $logger
    ) {
        $this->widgetManager = $widgetManager;
        $this->request = null;
        $this->router = $router;
        $this->requestStack = $requestStack;
        $this->logger = $logger;

        $this->config = [];
    }


    /**
     * @param AdapterInterface $cache
     */
    public function setCache(AdapterInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }


    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'renderWidget',
                [$this, 'renderWidget'],
                ['is_safe' => ['html'],
                    'needs_environment' => true]
            ),
            new \Twig_SimpleFunction(
                'renderWidgets',
                [$this, 'renderWidgets'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            new \Twig_SimpleFunction('getWidgetUrl', [$this, 'getWidgetUrl'], ['is_safe' => ['html']]),

            new \Twig_SimpleFunction('getSizeIcon', [$this, 'getSizeIcon'], ['is_safe' => ['html']]),

            new \Twig_SimpleFunction(
                'renderDashboard',
                [$this, 'renderDashboard'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),

            new \Twig_SimpleFunction(
                'renderTableCell',
                [$this, 'renderTableCell'],
                ['is_safe' => ['html'], 'needs_environment' => true,]
            ),

            new \Twig_SimpleFunction('getWidgetSize', [$this, 'getWidgetSize'], ['needs_environment' => true]),

            new \Twig_SimpleFunction('getWidgetStyle', [$this, 'getWidgetStyle'], [
                'is_safe' => ['html'],
                'needs_environment' => true,
            ]),


            new \Twig_SimpleFunction('widget_*', [$this, 'widget'], [
                'is_safe' => ['html'],
                'needs_environment' => true,
            ]),

        ];
    }


    /**
     * @param Twig_Environment $env
     * @param string $widgetName
     *
     * @return int
     * @throws \Twig_Error_Syntax
     * @throws \Twig_Error_Loader
     * @throws \Trinity\Bundle\WidgetsBundle\Exception\WidgetException
     */
    public function getWidgetSize(Twig_Environment $env, $widgetName)
    {
        $widget = $this->createWidget($widgetName, $env);
        return $widget->getSize();
    }


    /**
     * @param int $size
     *
     * @return string
     */
    public function getWidgetStyle($size)
    {
        if ($size === 24) {
            if (!($this->oddEven % 2)) {
                $this->oddEven++;
            }
            return 'long-widget';
        } else {
            if ($this->oddEven % 2) {
                $this->oddEven++;
                return 'left-widget';
            } else {
                $this->oddEven++;
                return 'right-widget';
            }
        }
    }


    /**
     * @param string $section
     * @param AbstractWidget $widget
     * @return string
     */
    public function getWidgetUrl($section, AbstractWidget $widget)
    {
        $url = '';
        switch ($section) {
            case WidgetManager::ACTION_REMOVE:
                $url = $this->router->generate('remove_widget', [
                    'widgetName' => $widget->getName(),
                ]);
                break;
            case WidgetManager::ACTION_RESIZE:
                $size = ($widget->getSize() === WidgetSizes::NORMAL) ? WidgetSizes::FULL : WidgetSizes::NORMAL;

                $url = $this->router->generate('resize_widget', [
                    'widgetName' => $widget->getName(),
                    'widgetSize' => $size,
                ]);

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
        $icon = ($widget->getSize() === WidgetSizes::NORMAL)
            ?
            '<i class="trinity trinity-plus" id="get-bigger"></i><i id="get-smaller" class="trinity trinity-minus" style="display: none"></i>'
            :
            '<i class="trinity trinity-minus" id="get-smaller"></i><i class="trinity trinity-plus" id="get-bigger" style="display: none"></i>';

        return $icon;
    }


    /**
     * @param Twig_Environment $env
     * @param mixed $object
     * @param int $attribute
     * @param string $widgetName
     * @param UserDashboardInterface $user
     * @return string
     * @throws \Twig_Error_Syntax
     * @throws \Twig_Error_Loader
     * @throws \Trinity\Bundle\WidgetsBundle\Exception\WidgetException
     */
    public function renderTableCell(
        Twig_Environment $env,
        $object,
        $attribute,
        $widgetName,
        UserDashboardInterface $user
    ) {
        /*$widget = */
        $this->createWidget($widgetName, $env);

        if (!is_array($object)) {
            try {
                $result = ObjectMixin::get($object, $attribute);
            } catch (\Exception $ex) {
                $result = '';
            }
        } elseif (array_key_exists($attribute, $object)) {
                $result = $object[$attribute];
        } else {
            $result = '';
        }

        //TODO internal function
        if ($this->template->hasBlock('widget_table_cell_' . $attribute, [])) {
            $result = $this->template->renderBlock(
                'widget_table_cell_' . $attribute,
                ['value' => $result, 'row' => $object]
            );
            return $result;
        } elseif ($result instanceof \DateTime) {
            $result = $this->template->renderBlock('widget_cell_datetime', ['value' => $result, 'row' => $object]);
        } elseif (is_bool($result)) {
            $result = $this->template->renderBlock('widget_cell_boolean', ['value' => $result, 'row' => $object]);
        } elseif (Strings::startsWith($result, "http") || Strings::startsWith($result, 'www')) {
            $result = $this->template->renderBlock('widget_cell_link', ['value' => $result, 'row' => $object]);
        } else {
            $result = $this->template->renderBlock('widget_cell_string', ['value' => $result, 'row' => $object]);
        }

        return $result;
    }


    /**
     * @param Twig_Environment $env
     * @param WidgetsDashboard $dashboard
     * @param UserDashboardInterface $user
     *
     * @return string
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\Form\Exception\LogicException
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \Twig_Error_Syntax
     * @throws \Twig_Error_Loader
     * @throws \Trinity\Bundle\WidgetsBundle\Exception\WidgetException
     */
    public function renderDashboard(Twig_Environment $env, WidgetsDashboard $dashboard, UserDashboardInterface $user)
    {

        $this->widgetManager->setUser($user); // set user for widget manager, so user dont have to be send in method
        $widgetsNames = $dashboard->getWidgets();
        $allWidgets = $this->widgetManager->getDashboardWidgets();
        $staticWidgets = $this->widgetManager->getStaticWidgets();
        $staticWidgetsNames = [];
        foreach ($staticWidgets as $widgetName => $widget) {
            $staticWidgetsNames[] = $widgetName;
        }
        $hiddenWidgetsNames = [];
        $showedWidgetsNames = [];
        /** @var WidgetsSettingsManager $widgetsSettingsManager */
        $widgetsSettingsManager = $user->getWidgetsSettingsManager();

        foreach ($allWidgets as $widgetName => $widget) {
            if (in_array($widgetName, $widgetsNames, false)) {
                $widgetSettings = $widgetsSettingsManager->getWidgetSettings($widgetName);
                if (array_key_exists('inOrder', $widgetSettings)) {
                    $inOrder = $widgetSettings['inOrder'];
                    $showedWidgetsNames[$inOrder] = $widgetName;
                } else {
                    $showedWidgetsNames[] = $widgetName;
                }
            } else {
                $hiddenWidgetsNames[] = $widgetName;
            }
        }
        ksort($showedWidgetsNames);


        /** @var \Twig_TemplateInterface $template */
        $template = $env->loadTemplate('WidgetsBundle::dashboard.html.twig');
        $form = $this->widgetManager->getForm();

        $context = [
            'showedWidgets' => $showedWidgetsNames,
            'hiddenWidgets' => $hiddenWidgetsNames,
            'staticWidgets' => $staticWidgetsNames,
            'form' => $form->createView(),
            'widgetLayout' => $this->widgetLayout,
            'globalSettings' => $widgetsSettingsManager->getWidgetSettings('globalSettings'),
            // if is globalSettings send from here, then it don't have to be asked on every renderWidget function
        ];

        return $template->render($context);
    }
    

    /**
     * @param string $widgetName
     * @param Twig_Environment $env
     * @param UserDashboardInterface|null $user
     *
     * @return AbstractWidget
     *
     * @throws \Twig_Error_Syntax
     * @throws \Twig_Error_Loader
     * @throws \Trinity\Bundle\WidgetsBundle\Exception\WidgetException
     */
    public function createWidget(string $widgetName, Twig_Environment $env)
    {
        /** @var AbstractWidget $widget */
        $widget = $this->widgetManager->createWidget($widgetName, true);
        /** @var Twig_Template $template */
        $this->template = $env->loadTemplate($widget->getTemplate());

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
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Twig_Error_Syntax
     * @throws \Twig_Error_Loader
     * @throws WidgetException
     */
    public function renderWidget(Twig_Environment $env, string $widgetName, array $options = [])
    {
        $wg = null;

        if ($this->config['cache']['enabled'] === false) {
            $cache = null;
        } else {
            $cache = $this->config['cache']['service'];
            $wg = $cache->getItem($widgetName);
            if ($wg->isHit()) {
                return $wg->get();
            }
        }


        try {
            $widget = $this->createWidget($widgetName, $env);
            $wb = $widget->buildWidget();

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
            
            
            
            if (!$this->widgetManager->isWidgetEmpty($widget)) {
                $body = $this->template->render($context);
            } elseif (!$options['globalSettings']['hideEmpty']) {
                $body = $env->loadTemplate('WidgetsBundle::widget_empty_layout.html.twig')->render($context);
            } else {
                return null;
            }

            if ($cache) {
                $wg->set($body);
                $wg->expiresAfter(new \DateInterval($this->config['cache']['cache_expiration_time']));
                $cache->save($wg);
            }
            return $body;
        } catch (\Exception $e) {
            $this->logger->addError($e);
            if (!$options['globalSettings']['hideBroken']) {
                return $env->loadTemplate('WidgetsBundle::widget_error_layout.html.twig')
                    ->render(
                        [
                            'name' => 'Missing Widget',
                            'routeName' => '',
                            'gridParameters' => '',
                            'title' => 'Missing Widget',
                            'size' => WidgetSizes::NORMAL,
                            'resizable' => false,
                            'removable' => false,
                        ]
                    );
            } else {
                return null;
            }
        }
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
