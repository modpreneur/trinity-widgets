<?php


namespace Trinity\WidgetsBundle\Controller;


use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Trinity\WidgetsBundle\Entity\WidgetsDashboard;


/**
 * Class WidgetsController
 * @package Trinity\WidgetsBundle\Controller
 *
 * @Route("/admin/widget/api")
 */
class WidgetsController extends Controller
{

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
            $em = $this->getDoctrine()->getEntityManager();

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