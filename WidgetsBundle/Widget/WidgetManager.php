<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\WidgetsBundle\Widget;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Trinity\FrameworkBundle\Entity\BaseUser;
use Trinity\WidgetsBundle\Entity\UserDashboardInterface;
use Trinity\WidgetsBundle\Entity\WidgetsDashboard;
use Trinity\WidgetsBundle\Exception\WidgetException;
use Trinity\WidgetsBundle\Form\DashboardType;


/**
 * Class WidgetManager.
 */
class WidgetManager
{

    const ACTION_REMOVE = 'remove';

    const ACTION_RESIZE = 'resize';


    /** @var  Router */
    protected $router;

    /** @var TokenStorage */
    protected $tokenStorage;

    /** @var  EntityManager */
    protected $em;

    /** @var  FormFactoryInterface  */
    protected $formFactory;

    /** @var Request */
    protected $request;

    /** @var AbstractWidgetInterface[] */
    protected $widgets = [];

    /** @var UserDashboardInterface */
    protected $user;

    /** @var string */
    protected $routeUrl;

    /** @var array */
    protected $routeParameters;

    /** @var bool */
    protected $redirect = false;

    /** @var array */
    protected $requestData = [];


    /**
     * WidgetManager constructor.
     * @param Router $router
     * @param TokenStorage $tokenStorage
     * @param EntityManager $em
     * @param FormFactoryInterface $formFactory
     * @param RequestStack $requestStack
     */
    public function __construct(Router $router, TokenStorage $tokenStorage, EntityManager $em, FormFactoryInterface $formFactory, RequestStack $requestStack)
    {
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->request = $requestStack->getCurrentRequest();
    }


    /**
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        //@todo @RichardBures k čemu to je?
        $redirectUrl = $this->getCurrentUri();

        if ($this->tokenStorage && $this->tokenStorage->getToken()) {
            $user = $this->tokenStorage->getToken()->getUser();

            if (!($user instanceof UserDashboardInterface)) {
                return;
            }

            /** @var WidgetsDashboard $dashboard */
            $dashboard = $user->getWidgetsDashboard();

            if ($this->isRedirected()) {
                $widget = null;

                if (array_key_exists(self::ACTION_REMOVE, $this->requestData)) {
                    $widget = ($this->getWidget($this->requestData[self::ACTION_REMOVE]));

                    if ($widget instanceof RemovableInterface) {
                        $widget->remove();
                        $dashboard->removeWidget($widget);
                        $this->em->persist($dashboard);
                        $this->em->flush();
                    }
                }

                $event->setController(
                    function () use ($redirectUrl) {
                        return new RedirectResponse($redirectUrl);
                    }
                );
            }
        }

    }


    /**
     * @return string
     */
    public function getCurrentUri()
    {
        if($this->request){
            return $this->request->getScheme().'://'.$this->request->getHttpHost().$this->request->getBaseUrl(
            ).$this->request->getPathInfo();
        }

        return null;
    }


    /**
     * @return bool
     */
    public function isRedirected()
    {
        $choices = ['user', self::ACTION_REMOVE, self::ACTION_RESIZE];

        foreach ($choices as $hash) {
            //@todo @RicharBures nevíš že ten request bude vždycky, někdy ti to tady spadne na tom že je request null a na null nelze volat funkci get
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
     * @return AbstractWidgetInterface
     */
    private function getWidget($name)
    {
        return $this->widgets[$name];
    }


    /**
     * @param string $name widget name
     * @param bool $clone -> new instance of widget
     * @return AbstractWidgetInterface
     * @throws WidgetException
     */
    public function createWidget($name, $clone = true)
    {
        /** @var AbstractWidgetInterface $widget */
        $widget = $clone ? clone $this->getWidget($name) : $this->getWidget($name);

        return $widget;
    }


    /**
     * @param AbstractWidgetInterface $widget
     * @param callback|null $callback
     *
     * @throws WidgetException
     */
    public function addWidget(AbstractWidgetInterface $widget, $callback = null)
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
     * @return UserDashboardInterface
     */
    public function getUser()
    {
        return $this->user ;
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
        $form = $this->formFactory->create(DashboardType::class);
        $form->handleRequest($this->request);

        $user = $this->tokenStorage->getToken()->getUser();


        /** @var WidgetsDashboard $dashboard */
        $dashboard = $user->getWidgetsDashboard();

        if ($form->isSubmitted()) {
            $data = $form->getData();
            $dashboard->setWidgets($data['widgets']);

            $this->em->persist($dashboard);
            $this->em->flush();

            $this->redirect = true;

        } else {
            $form->setData(['widgets' => $dashboard->getWidgets()]);
        }

        return $form;
    }

}