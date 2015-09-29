<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\WidgetsBundle\Widget;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Security;
use Trinity\WidgetsBundle\Exception\WidgetException;
use Trinity\WidgetsBundle\Tests\Widgets\TestWidget;


/**
 * Class WidgetManager.
 */
class WidgetManager
{
    /**
     * @var string
     */
    protected $routeUrl;
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var Session;
     */
    protected $session;
    /**
     * @var Security
     */
    protected $securityContext;
    /** @var array */
    protected $routeParameters;
    protected $choices = ["widget_remove"];
    /** @var bool */
    protected $redirect = false;
    /** @var array */
    protected $requestData = [];
    /** @var AbstractWidget[] */
    private $widgets = [];
    /** @var  Router */
    private $router;


    /**
     * @param \Symfony\Component\DependencyInjection\Container $container
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->router = $container->get('router');
        $this->request = $container->get('request');
        $this->session = $this->request->getSession();


        $this->routeParameters = $this->request->attributes->all();
        foreach (array_keys($this->routeParameters) as $key) {
            if (substr($key, 0, 1) == '_') {
                unset($this->routeParameters[$key]);
            }
        }

        if ($this->isRedirected()) {
            return new RedirectResponse($this->getCurrentUri());
        }
    }


    /**
     * @return bool
     */
    public function isRedirected()
    {
        foreach ($this->choices as $hash) {
            $this->requestData = (array)$this->request->get($hash);
            if (count($this->requestData) > 0) {
                $this->redirect = true;
                break;
            }
        }

        return $this->redirect;
    }


    protected function getCurrentUri()
    {
        return $this->request->getScheme().'://'.$this->request->getHttpHost().$this->request->getBaseUrl(
        ).$this->request->getPathInfo();
    }


    /**
     * @param string $name widget name
     * @param bool $clone -> new instance of widget
     * @return TestWidget
     * @throws WidgetException
     */
    public function createWidget($name, $clone = true)
    {
        /** @var AbstractWidget $widget */
        $widget = $clone ? clone $this->getWidget($name) : $this->getWidget($name);

        return $widget;
    }


    /**
     * @param string $name
     * @return AbstractWidget
     */
    private function getWidget($name)
    {
        return $this->widgets[$name];
    }


    /**
     * @param AbstractWidget $widget
     * @param callback|null $callback
     *
     * @throws WidgetException
     */
    public function addWidget(AbstractWidget $widget, $callback = null)
    {
        if (!array_key_exists($widget->getName(), $this->widgets)) {
            $this->widgets[$widget->getName()] = $widget;
            $widget->setManager($this);
        } else {
            throw new WidgetException('This widget is already registered.');
        }

        if ($callback && is_callable($callback)) {
            call_user_func($callback, $widget);
        }
    }


    /**
     * Returns Route URL
     *
     * @return string
     */
    public function getRouteUrl()
    {
        if ($this->routeUrl === null) {
            $this->routeUrl = $this->router->generate($this->request->get('_route'), $this->getRouteParameters());
        }

        return $this->routeUrl;
    }


    /**
     * @return array
     */
    public function getRouteParameters()
    {
        return $this->routeParameters;
    }


    /**
     * @param array $routeParameters
     */
    public function setRouteParameters($routeParameters)
    {
        $this->routeParameters = $routeParameters;
    }


}
