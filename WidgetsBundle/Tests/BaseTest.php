<?php
/*
 * This file is part of the Trinity project.
 */

namespace Trinity\WidgetsBundle\Tests;

use Trinity\FrameworkBundle\Utils\BaseWebTest;


/**
 * Class BaseTest.
 */
abstract class BaseTest extends BaseWebTest
{

    public function getWidgetExtension()
    {
        $em = $this->getMockBuilder('Trinity\WidgetsBundle\Twig\WidgetExtension')
            ->setConstructorArgs([$this->getContainer()])
            ->getMock();

        return $em;
    }

}
