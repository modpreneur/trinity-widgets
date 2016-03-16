<?php

namespace Trinity\Bundle\WidgetsBundle\Tests\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


/**
 * Class TestController
 * @package Trinity\Bundle\WidgetsBundle\Tests\Controller
 *
 * @Route("/")
 */
class TestController extends Controller
{

    /**
     * @Route("/", name="index")
     */
    public function indexAction()
    {

    }

}