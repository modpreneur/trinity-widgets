<?php
/**
 * This file is part of Trinity package.
 */

namespace Trinity\Bundle\WidgetsBundle\Controller;


use Doctrine\ORM\EntityManager;
use Exception;
use Nette\Utils\Json;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Trinity\Bundle\WidgetsBundle\Entity\WidgetsDashboard;
use Trinity\Bundle\WidgetsBundle\Entity\WidgetsSettingsManager;
use Trinity\Bundle\WidgetsBundle\Form\DashboardType;
use Trinity\Bundle\WidgetsBundle\Widget\RemovableInterface;
use Trinity\Bundle\WidgetsBundle\Widget\ResizableInterface;
use Trinity\Bundle\WidgetsBundle\Widget\WidgetManager;


/**
 * Class WidgetsController
 * @package Trinity\Bundle\WidgetsBundle\Controller
 *
 * @Route("/widget")
 */
class WidgetsController extends Controller
{


    /**
     * @Route("/manage", name="widget-manage", defaults={"_format": "json"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function manageDashboardWidgets(Request $request){

        $form =  $this->createForm(DashboardType::class);
        $form->handleRequest($request);

        if ($request->request->has('dashboard')
            && isset($request->request->get('dashboard')['widgets'])
        ) {
            $expandedWidgets = [];
            if (isset($request->request->get('dashboard')['expandedWidgets'])) {
                $expandedWidgets = $request->request->get('dashboard')['expandedWidgets'];
            }
            $em =$this->get('doctrine.orm.entity_manager');
            $widgetManager = $this->get('trinity.widgets.manager');
            $widgets = $request->request->get('dashboard')['widgets'];

            $user = $this->getUser();
            $widgetsSettingsManager = $user->getWidgetsSettingsManager();
            $availableIndex=0;

            $allWidgets = $widgetManager->getDashboardWidgets();

            foreach ($widgets as $widget) {
                if (array_key_exists('inOrder', $widgetsSettingsManager->getWidgetSettings($widget))) {
                    if ($widgetsSettingsManager->getWidgetSettings($widget)['inOrder'] >= $availableIndex) {
                        $availableIndex = $widgetsSettingsManager->getWidgetSettings($widget)['inOrder'] + 1;
                    }
                } else {
                    $widgetsSettingsManager->setWidgetSettings($widget, ['inOrder'=>-1]);
                }

                if (!in_array($widget, $widgets)) {
                    $widgetsSettingsManager->setWidgetSettings($widget, [
                        'inOrder'=> -1,
                    ]);
                } else {
                    if ($widgetsSettingsManager->getWidgetSettings($widget)['inOrder']===-1) {
                        $widgetsSettingsManager->setWidgetSettings($widget, [
                            'inOrder'=> $availableIndex,
                        ]);
                        $availableIndex++;
                    }
                }

                if (in_array($widget, $expandedWidgets)) {
                    $widgetsSettingsManager->setWidgetSettings($widget, [
                        'size' => 24,
                    ]);
                } else {
                    $widgetsSettingsManager->setWidgetSettings($widget, [
                        'size' => 12,
                    ]);
                }
            }

            /** @var WidgetsDashboard $dashboard */
            $dashboard = $user->getWidgetsDashboard();
            $dashboard->setWidgets($widgets);


            try {
                $em->persist($dashboard);
                $em->persist($widgetsSettingsManager);
                $em->flush();
            } catch (Exception $e) {
                return new JsonResponse(array('error'=>$e), 400);
            }
            return new JsonResponse(array('message'=>'success update','widgets'=>$form->getData()['widgets']), 200);
        }

        return new JsonResponse(array('error'=>'request failed'), 400);

    }

    /**
     * @Route("/resize/{widgetName}/{widgetSize}", name="resize_widget", defaults={"_format": "json"})
     *
     * @param  string $widgetName
     * @param int $widgetSize
     *
     * @return JsonResponse
     */
    public function resizeAction($widgetName, $widgetSize)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $widgetsSettingsManager = $this->getUser()->getWidgetsSettingsManager();

        $widgetsSettingsManager->setWidgetSettings($widgetName, [
            'size'=> $widgetSize,
        ]);

        try {
            $em->persist($widgetsSettingsManager);
            $em->flush($widgetsSettingsManager);
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
        return new JsonResponse(['status'=>'success'], 200);
    }


    /**
     * @Route("/remove/{widgetName}/", name="remove_widget", defaults={"_format": "json"})
     * @param string $widgetName
     *
     * @return JsonResponse
     */
    public function removeAction($widgetName)
    {

        try {
            /** @var EntityManager $em */
            $em = $this->get('doctrine.orm.entity_manager');

            /** @var WidgetsDashboard $dashboard */
            $dashboard = $this->getUser()->getWidgetsDashboard();
            $widgetsSettingsManager = $this->getUser()->getWidgetsSettingsManager();

            $widgetsSettingsManager->setWidgetSettings($widgetName, [
                'inOrder'=> -1,
            ]);

            if (!$dashboard->removeWidget($widgetName)) {
                return new JsonResponse(['error' => 'Widget could not be deleted'], 400);
            }

            $em->persist($dashboard);
            $em->persist($widgetsSettingsManager);
            $em->flush();


        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }

        return new JsonResponse(['status'=>'success'], 200);
    }


    /**
     * @Route("/changeOrder/{order}/", name="change_order_widget", defaults={"_format": "json"})
     *
     * @param $order
     * @throws \Nette\Utils\JsonException
     * @throws \LogicException
     *
     * @return JsonResponse
     */
    public function changeOrder($order)
    {
        $orderArr = Json::decode($order);

        $em = $this->get('doctrine.orm.entity_manager');
        $widgetsSettingsManager = $this->getUser()->getWidgetsSettingsManager();

        $orderArrCount = count($orderArr);
        for ($i = 0; $i < $orderArrCount; $i++) {
//            $widgetsSettingsManager->getWidgetSettings($orderArr[$i]);
            $widgetsSettingsManager->setWidgetSettings($orderArr[$i], [
                'inOrder' => $i,
            ]);
        }

        try {
            $em->persist($widgetsSettingsManager);
            $em->flush();

            return new JsonResponse(['status' => 'success'], 200);
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }


    /**
     * @param string $widgetName
     * @throws \LogicException
     * @throws \Trinity\Bundle\WidgetsBundle\Exception\WidgetException
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function ajaxRenderWidget($widgetName)
    {
        $widgetManager = $this->get('trinity.widgets.manager');

        /** @var AbstractWidget $widget */
        $widget = $widgetManager->createWidget($widgetName, true, $this->getUser());
        $size = $widget->getSize();
        $widgetSettings = $this->getUser()->getWidgetsSettingsManager()->getWidgetSettings($widgetName);

        if (array_key_exists('size', $widgetSettings)) {
            $size = $widgetSettings['size'];
        }
        /** @var \Twig_TemplateInterface $template */
        $template = $widget->getTemplate();
        $wb = $widget->buildWidget();
        $context = [
            'name' => $widget->getName(),
            'routeName' => $widget->getRouteName(),
            'gridParameters' => $widget->getGridParameters(),
            'widget' => $widget,
            'title' => $widget->getTitle(),
            'size' => $size,
            'resizable' => $widget instanceof ResizableInterface,
            'removable' => $widget instanceof RemovableInterface,
        ];
        if ($wb && is_array($wb)) {
            $context = array_merge($context, $wb);
        }
        
        return $this->renderView($template, $context);
    }

    /**
     * @Route("/render/", name="ajax_render_widgets")
     * @throws \Trinity\Bundle\WidgetsBundle\Exception\WidgetException
     * @throws \LogicException
     * @return array
     */
    public function ajaxRenderWidgets()
    {
        $dashboard = $this->getUser()->getWidgetsDashboard();
        $widgetsSettingsManager = $this->getUser()->getWidgetsSettingsManager();
        $widgetsNames = $dashboard->getWidgets();
        $orderedWidgetsNames = [];

        foreach ($widgetsNames as $widgetName) {
            $widgetSettings = $widgetsSettingsManager->getWidgetSettings($widgetName);
            $inOrder = $widgetSettings['inOrder'];
            $orderedWidgetsNames[$inOrder] = $widgetName;
        }
        ksort($orderedWidgetsNames);

        $widgetsHTML = [];
        foreach ($orderedWidgetsNames as $widgetName) {
            $widgetsHTML[$widgetName] = $this->ajaxRenderWidget($widgetName);
        }

        return new JsonResponse(['message'=>'test', 'widgets'=> $widgetsHTML], 200);
    }
}