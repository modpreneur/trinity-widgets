<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\Bundle\WidgetsBundle\Widget;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Trinity\Bundle\WidgetsBundle\Entity\UserDashboardInterface;
use Trinity\Bundle\WidgetsBundle\Entity\WidgetsDashboard;
use Trinity\Bundle\WidgetsBundle\Entity\WidgetsSettingsManager;
use Trinity\Bundle\WidgetsBundle\Exception\WidgetException;
use Trinity\Bundle\WidgetsBundle\Form\DashboardType;

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

    /** @var  FormFactoryInterface */
    protected $formFactory;

    /** @var RequestStack */
    protected $requestStack;

    /** @var AbstractWidget[] */
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
    public function __construct(
        Router $router,
        TokenStorage $tokenStorage,
        EntityManager $em,
        FormFactoryInterface $formFactory,
        RequestStack $requestStack
    )
    {
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->requestStack = $requestStack;
    }


    /**
     * @param FilterControllerEvent $event
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \InvalidArgumentException
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if ($this->requestStack->getCurrentRequest()->attributes->get('_route') !== 'admin_home') {
            return;
        }

        $redirectUrl = $this->getCurrentUri();
        $user = $this->getUser();

        if ($user !== null) {
            if (!($user instanceof UserDashboardInterface)) {
                return;
            }

            /** @var WidgetsDashboard $dashboard */
            $dashboard = $user->getWidgetsDashboard();

            if ($this->isRedirected()) {
                /** @var AbstractWidget $widget */
                $widget = null;

                if (array_key_exists(self::ACTION_REMOVE, $this->requestData)) {
                    $widget = $this->getWidget($this->requestData[self::ACTION_REMOVE]);

                    if ($widget instanceof RemovableInterface) {
//                        $widget->remove();
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
        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            return $request->getScheme() . '://' . $request->getHttpHost() . $request->getBaseUrl() . $request->getPathInfo();
        }

        return null;
    }


    /**
     * @return bool
     */
    public function isRedirected()
    {
        $choices = ['user', self::ACTION_REMOVE, self::ACTION_RESIZE];

        $request = $this->requestStack->getCurrentRequest();

        if ($request) {
            foreach ($choices as $hash) {
                if ($request->get($hash)) {
                    $this->requestData[$hash] = $request->get($hash);
                }
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
     * @param UserDashboardInterface $user
     * @return AbstractWidget
     * @throws WidgetException
     */
    public function createWidget($name, $clone = true, UserDashboardInterface $user = null)
    {
        /** @var AbstractWidget $widget */
        $widget = $clone ? clone $this->getWidget($name) : $this->getWidget($name);

        $widgetManager = null;

        if ($user === null) {
            $user = $this->getUser();
        }
        if ($user !== null) {
            $widgetManager = $user->getWidgetsSettingsManager();
            $widgetSettings = $widgetManager->getWidgetSettings($name);
            if (array_key_exists('size', $widgetSettings)) {
                $widget->setSize((int)$widgetSettings['size']);
            }
        }

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
            $callback($widget);
        }
    }


    /**
     * Returns Route URL
     *
     * @return string
     * @throws \Symfony\Component\Routing\Exception\RouteNotFoundException
     * @throws \Symfony\Component\Routing\Exception\MissingMandatoryParametersException
     * @throws \Symfony\Component\Routing\Exception\InvalidParameterException
     */
    public function getRouteUrl()
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($this->routeUrl === null && $request) {
            $this->routeUrl = $this->router->generate($request->get('_route'), $this->getRouteParameters());
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
     * @param UserDashboardInterface $user
     */
    public function setUser(UserDashboardInterface $user)
    {
        $this->user = $user;
    }


    /**
     * @return UserDashboardInterface
     */
    public function getUser()
    {
        if ($this->user === null) {
            $token = $this->tokenStorage->getToken();
            if ($token !== null) {
                return $this->tokenStorage->getToken()->getUser();
            }
        }
        return $this->user;
    }


    /**
     * Return widgets name
     * @return array
     */
    public function getDashboardWidgets()
    {
        $widgets = [];


        foreach ($this->widgets as $item) {
            if ($item->getType() === 'dashboard') {
                $title = ucfirst(str_replace('_', ' ', $item->getName()));

                $widgets[$item->getName()] = $title;
            }
        }

        return $widgets;
    }


    /**
     * Return widgets name
     * @return array
     */
    public function getStaticWidgets()
    {
        $widgets = [];
        foreach ($this->widgets as $item) {
            if ($item->getType() === 'static') {
                $title = ucfirst(str_replace('_', ' ', $item->getName()));

                $widgets[$item->getName()] = $title;
            }
        }

        return $widgets;
    }

    public function getFlippedDashboardWidgets()
    {
        return array_flip($this->getDashboardWidgets());

    }

    public function getBigWidgets()
    {
        $user = $this->getUser();

        /** @var WidgetsSettingsManager $widgetsSettingsManager */
        $widgetsSettingsManager = $user->getWidgetsSettingsManager();
        $bigWidgets = [];
        foreach ($this->widgets as $item) {
            $widgetSetting = $widgetsSettingsManager->getWidgetSettings($item->getName());
            if (array_key_exists('size', $widgetSetting) &&
                ($widgetSetting['size'] === '24' || $widgetSetting['size'] === 24)
            ) { // todo find out why size changet from int to string or whot is going there now
                $bigWidgets[] = $item->getName();
            }
        }
        return $bigWidgets;
    }

    /**
     * @param AbstractWidget $widget
     * @return bool
     */
    public function isWidgetEmpty($widget)
    {

        $wb = $widget->buildWidget();
        if ($widget instanceof TableWidget) {
            return !$wb['body'];
        } elseif ($widget instanceof ChartWidget) {
            return !array_key_exists('chart', $wb);
        }

    }

    /**
     * @return array|AbstractWidget
     */
    public function getGlobalSettings()
    {
        $user = $this->getUser();

        /** @var WidgetsSettingsManager $widgetsSettingsManager */
        $widgetsSettingsManager = $user->getWidgetsSettingsManager();

        return $widgetsSettingsManager->getWidgetSettings('globalSettings');
    }

    /**
     * @return \Symfony\Component\Form\Form
     * @throws \Symfony\Component\Form\Exception\LogicException
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function getForm()
    {
        $request = $this->requestStack->getCurrentRequest();

        $form = $this->formFactory->create(DashboardType::class);
        $form->handleRequest($request);

        /** @var UserDashboardInterface $user */
        $user = $this->getUser();


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