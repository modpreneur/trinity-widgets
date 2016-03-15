<?php
/**
 * This file is part of Trinity package.
 */

namespace Trinity\WidgetsBundle\Controller;


use Doctrine\ORM\EntityManager;
use Exception;
use Nette\Utils\Json;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Trinity\WidgetsBundle\Entity\WidgetsDashboard;
use Trinity\WidgetsBundle\Entity\WidgetsSettingsManager;
use Trinity\WidgetsBundle\Form\DashboardType;


/**
 * Class WidgetsController
 * @package Trinity\WidgetsBundle\Controller
 *
 * @Route("/admin/widget")
 */
class WidgetsController extends Controller
{

    /**
     * @Route("/manage", name="widget-manage")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function manageDashboardWidgets(Request $request){

        $form =  $this->createForm(DashboardType::class);
        $form->handleRequest($request);
        if(
            $request->request->has('dashboard')
            && isset($request->request->get('dashboard')['widgets']))
        {
            $expandedWidgets = [];
            if(isset($request->request->get('dashboard')['expandedWidgets']))
            {
                $expandedWidgets = $request->request->get('dashboard')['expandedWidgets'];
            }
            $em =$this->get('doctrine.orm.entity_manager');
            $widgetManager = $this->get('trinity.widgets.manager');
            $widgets = $request->request->get('dashboard')['widgets'];

            $user = $this->getUser();
            $widgetsSettingsManager = $user->getWidgetsSettingsManager();
            $availableIndex=0;

            $allWidgets = $widgetManager->getDashboardWidgets();

            foreach($widgets as $widget)
            {
                if(array_key_exists('inOrder',$widgetsSettingsManager->getWidgetSettings($widget))) {
                    if ($widgetsSettingsManager->getWidgetSettings($widget)['inOrder'] >= $availableIndex) {
                        $availableIndex = $widgetsSettingsManager->getWidgetSettings($widget)['inOrder'] + 1;
                    }
                }
                else{
                    $widgetsSettingsManager->setWidgetSettings($widget,array('inOrder'=>-1));
                }

                if(!in_array($widget,$widgets))
                {
                    $widgetsSettingsManager->setWidgetSettings($widget,array(
                        'inOrder'=> -1,
                    ));
                }else
                {
                    if($widgetsSettingsManager->getWidgetSettings($widget)['inOrder']==-1)
                    {
                        $widgetsSettingsManager->setWidgetSettings($widget,array(
                            'inOrder'=> $availableIndex,
                        ));
                        $availableIndex++;
                    }
                }

                if(in_array($widget,$expandedWidgets))
                {
                    $widgetsSettingsManager->setWidgetSettings($widget, array(
                        'size' => 24,
                    ));
                }
                else{
                    $widgetsSettingsManager->setWidgetSettings($widget, array(
                        'size' => 12,
                    ));
                }
            }



            /** @var WidgetsDashboard $dashboard */
            $dashboard = $user->getWidgetsDashboard();
            $dashboard->setWidgets($widgets);


            $em->persist( $dashboard );
            $em->persist($widgetsSettingsManager);
            try
            {
                $em->flush();
            }catch(\Doctrine\DBAL\DBALException $e)
            {
                return new JsonResponse(array('error'=>$e), 400);
            }
            return new JsonResponse(array('message'=>'success update','widgets'=>$form->getData()['widgets']), 200);
        }

        return new JsonResponse(array('error'=>'request failed'), 400);

    }

    /**
     * @Route("/resize/{widgetName}/{widgetSize}", name="resize_widget")
     *
     * @param  string $widgetName
     * @param int $widgetSize
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function resizeAction($widgetName,$widgetSize)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $widgetsSettingsManager = $this->getUser()->getWidgetsSettingsManager();


        $widgetsSettingsManager->setWidgetSettings($widgetName,array(
            'size'=> $widgetSize,
        ));

        $em->persist($widgetsSettingsManager);
        try{

            $em->flush($widgetsSettingsManager);

        }catch(Exception $e){
            return new JsonResponse(array('error' => $e->getMessage()), 400);
        }
        return new JsonResponse(array('status'=>'success'), 200);
    }

    /**
     * @Route("/remove/{widgetName}/", name="remove_widget")
     * @param string $widgetName
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAction($widgetName)
    {

        try {
            /** @var EntityManager $em */
            $em = $this->get('doctrine.orm.entity_manager');

            /** @var WidgetsDashboard $dashboard */
            $dashboard = $this->getUser()->getWidgetsDashboard();
            $widgetsSettingsManager = $this->getUser()->getWidgetsSettingsManager();

            $widgetsSettingsManager->setWidgetSettings($widgetName,array(
                'inOrder'=> -1,
            ));

            if (!$dashboard->removeWidget($widgetName)) {
                return new JsonResponse(array('error' => 'Widget could not be deleted'), 400);
            }

            $em->persist($dashboard);
            $em->persist($widgetsSettingsManager);
            $em->flush();


        } catch (Exception $e) {
            return new JsonResponse(array('error' => $e->getMessage()), 400);
        }

        return new JsonResponse(array('status'=>'success'), 200);
    }

    /**
     * @Route("/changeOrder/{order}/", name="change_order_widget")
     *
     * @param $order
     * @return JsonResponse
     * @throws \Nette\Utils\JsonException
     */
    public function changeOrder($order)
    {
        $orderArr = Json::decode($order);

        try {
            $em = $this->get('doctrine.orm.entity_manager');
            $widgetsSettingsManager = $this->getUser()->getWidgetsSettingsManager();

            for ($i = 0; $i < count($orderArr); $i++) {

//                $widgetsSettingsManager->getWidgetSettings($orderArr[$i]);
                $widgetsSettingsManager->setWidgetSettings($orderArr[$i], array(
                    'inOrder' => $i,
                ));


            }

            $em->persist($widgetsSettingsManager);
            $em->flush();

            return new JsonResponse(array('status' => 'success'), 200);

        }catch(Exception $e)
        {
            return new JsonResponse(array('error' => $e->getMessage()), 400);
        }
    }
}