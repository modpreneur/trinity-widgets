<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\WidgetsBundle\Widget;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Trinity\FrameworkBundle\Entity\BaseUser;
use Trinity\WidgetsBundle\Entity\IUserDashboard;
use Trinity\WidgetsBundle\Entity\WidgetsDashboard;
use Trinity\WidgetsBundle\Exception\WidgetException;


/**
 * Class WidgetManager.
 */
class WidgetManager
{

    const ACTION_REMOVE = 'remove';

    const ACTION_SMALLER = 'smaller';

    const ACTION_BIGGER = 'bigger';


    /**
     * @var string
     */
    protected $routeUrl;
    /**
     * @var Request
     */
    protected $request;

    /** @var TokenStorage */
    protected $tokenStorage;

    /** @var array */
    protected $routeParameters;

    /** @var bool */

    /** @var bool */
    protected $redirect = false;

    /** @var array */
    protected $requestData = [];

    /** @var AbstractWidget[] */
    protected $widgets = [];

    /** @var  Router */
    protected $router;

    /** @var IUserDashboard */
    protected $user;


    /**
     * WidgetManager constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->router = $container->get('router');
        $this->tokenStorage = $this->container->get('security.token_storage');
    }


    public function setRequest($request){
        $this->request = $request;

        if($request && $request->attributes){
            $this->routeParameters = $this->request->attributes->all();
            foreach (array_keys($this->routeParameters) as $key) {
                if (substr($key, 0, 1) == '_') {
                    unset($this->routeParameters[$key]);
                }
            }
        }
    }


    /**
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $em = $this->container->get('doctrine')->getManager();
        $redirectUrl = $this->getCurrentUri();

        if ($this->tokenStorage && $this->tokenStorage->getToken()) {
            $user = $this->tokenStorage->getToken()->getUser();

            if (!($user instanceof IUserDashboard)) {
                return;
            }

            /** @var WidgetsDashboard $dashboard */
            $dashboard = $user->getWidgetsDashboard();

            if ($this->isRedirected()) {
                $widget = null;

                if (array_key_exists(self::ACTION_REMOVE, $this->requestData)) {
                    $widget = ($this->getWidget($this->requestData[self::ACTION_REMOVE]));

                    if ($widget instanceof IRemovable) {
                        $widget->remove();
                        $dashboard->removeWidget($widget);
                        $em->persist($dashboard);
                        $em->flush();
                    }
                }

                $event->setController(
                    function () use ($redirectUrl) {
                        return new RedirectResponse($redirectUrl);
                    }
                );
            }
        }

        if (($this->session->get('widget_redirect') && $this->session->get('widget_redirect') == '1')) {
            $this->session->set('widget_redirect', '0');

            $event->setController(
                function () use ($redirectUrl) {
                    return new RedirectResponse($redirectUrl);
                }
            );
        }

    }


    /**
     * @return string
     */
    public function getCurrentUri()
    {
        return $this->request->getScheme().'://'.$this->request->getHttpHost().$this->request->getBaseUrl(
        ).$this->request->getPathInfo();
    }


    /**
     * @return bool
     */
    public function isRedirected()
    {
        $choices = ['user', self::ACTION_REMOVE, self::ACTION_BIGGER, self::ACTION_SMALLER];

        foreach ($choices as $hash) {
            if ($this->request->get($hash)) {
                $this->requestData[$hash] = $this->request->get($hash);
            }
        }

        if (count($this->requestData) > 1) {
            $this->redirect = true;
        }

        return $this->redirect;
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
            $name = $widget->getName();
            throw new WidgetException("This widget($name) is already registered.");
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
        if ($this->routeUrl === null && $this->request) {
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


    /**
     * @param BaseUser $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }


    /**
     * Return widgets name
     * @return array
     */
    public function getDashboardWidgets()
    {
        $widgets = [];

        foreach ($this->widgets as $item) {
            if ($item->getType() == "dashboard") {
                $title = ucfirst(str_replace("_", " ", $item->getName()));

                $widgets[$item->getName()] = $title;
            }
        }

        return $widgets;
    }


    /**
     * @return \Symfony\Component\Form\Form
     */
    public function getForm()
    {
        $form = $this->container->get('form.factory')->create('trinity_widgets_bundle_dashboard_type');
        $form->handleRequest($this->container->get('request'));


        $em = $this->container->get('doctrine')->getManager();
        $user = $this->tokenStorage->getToken()->getUser();


        /** @var WidgetsDashboard $dashboard */
        $dashboard = $user->getWidgetsDashboard();

        if ($form->isSubmitted()) {
            $data = $form->getData();
            $dashboard->setWidgets($data['widgets']);

            $em->persist($dashboard);
            $em->flush();

            $this->redirect = true;
            $this->session->set('widget_redirect', '1');

        } else {
            $form->setData(['widgets' => $dashboard->getWidgets()]);
        }

        return $form;
    }

}
