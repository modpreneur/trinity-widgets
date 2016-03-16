<?php
/*
 * This file is part of the Trinity project.
 */

namespace Trinity\Bundle\WidgetsBundle\Tests;

use Trinity\FrameworkBundle\Utils\BaseWebTest;


/**
 * Class BaseTest.
 */
abstract class BaseTest extends BaseWebTest
{

    public function getWidgetExtension()
    {
        $em = $this->getMockBuilder('Trinity\Bundle\WidgetsBundle\Twig\WidgetExtension')
            ->setConstructorArgs([$this->getContainer()])
            ->getMock();

        return $em;
    }

}
