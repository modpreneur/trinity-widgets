<?php


namespace Trinity\WidgetsBundle\Controller;


use Doctrine\ORM\EntityManager;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Trinity\WidgetsBundle\Entity\WidgetsDashboard;


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

        $form =  $this->createForm('trinity_widgets_bundle_dashboard_type');
        $form->handleRequest($request);

        if(
            $request->request->has('trinity_widgets_bundle_dashboard_type')
            && isset($request->request->get('trinity_widgets_bundle_dashboard_type')['widgets']))
        {
            $widgets = $request->request->get('trinity_widgets_bundle_dashboard_type')['widgets'];
            $user    = $this->getUser();

            /** @var WidgetsDashboard $dashboard */
            $dashboard = $user->getWidgetsDashboard();

            $dashboard->setWidgets($widgets);

            $this->get('doctrine.orm.entity_manager')->persist( $dashboard );
            $this->get('doctrine.orm.entity_manager')->flush( $dashboard );
        }

        $redirect = $request->headers->get('referer');
        if(null === $redirect){
            $redirect = "http://" . $request->headers->get('host');
        }

        return $this->redirect($redirect);
    }


    /**
     * @Route("/remove/{widgetName}/", name="remove_widget")
     * @param string $widgetName
     * @return JsonResponse
     */
    public function removeAction($widgetName)
    {
        $status = 200;
        $message = 'Ok';

        try {
            /** @var EntityManager $em */
            $em = $this->get('doctrine.orm.entity_manager');

            /** @var WidgetsDashboard $dashboard */
            $dashboard = $this->getUser()->getWidgetsDashboard();

            if (!$dashboard->removeWidget($widgetName)) {
                $message = "Error";
            }

            $em->persist($dashboard);
            $em->flush();

        } catch (Exception $e) {
            $status = 500;
            $message = $e->getMessage();
        }

        return new JsonResponse(['status' => $status, 'message' => $message]);
    }

}