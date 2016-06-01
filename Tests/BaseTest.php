<?php
/*
 * This file is part of the Trinity project.
 */

namespace Trinity\Bundle\WidgetsBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


/**
 * Class BaseTest.
 */
abstract class BaseTest extends WebTestCase
{

    function getContainer(){

        $client = self::createClient();
        $container = $client->getContainer();

        return $container;
    }

    public function getWidgetExtension()
    {
        $em = $this->getMockBuilder('Trinity\Bundle\WidgetsBundle\Twig\WidgetExtension')
            ->setConstructorArgs([$this->getContainer()])
            ->getMock();

        return $em;
    }

}
