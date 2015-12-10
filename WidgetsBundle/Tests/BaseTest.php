<?php


/*
 * This file is part of the Trinity project.
 */

namespace Trinity\WidgetsBundle\Tests;

use Braincrafted\Bundle\TestingBundle\Test\WebTestCase;
use Entity\User;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


/**
 * Class BaseTest.
 */
abstract class BaseTest extends WebTestCase
{
    /** @var  Container */
    protected $container;

    /** @var  KernelInterface */
    protected $kernelObject;

    /** @var  Client */
    protected $client;


    public function setUp()
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->logIn();
    }


    private function logIn()
    {
        $firewall = 'dev';
        $user = new User();
        $this->setPropertyValue($user, 'id', 1);
        $token = new UsernamePasswordToken('ryanpass', null, $firewall, array('ROLE_ADMIN'));
        $token->setUser($user);

        $this->client->getContainer()->get('security.token_storage')->setToken($token);

        $session = $this->client->getContainer()->get('session');
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }


    /**
     * @param string|object $class
     * @param string $name
     *
     * @return \ReflectionMethod
     */
    protected static function getMethod($class, $name)
    {
        $class = new ReflectionClass($class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }


    public function getWidgetExtension()
    {
        $em = $this->getMockBuilder('Trinity\WidgetsBundle\Twig\WidgetExtension')->setConstructorArgs(
                [$this->getContainer()]
            )->getMock();

        return $em;
    }


    /**
     * @param object $class
     * @param string $property
     * @param $value
     */
    protected function setPropertyValue($class, $property, $value)
    {
        $property = new \ReflectionProperty($class, $property);
        $property->setAccessible(true);
        $property->setValue($class, $value);
    }


    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEM()
    {
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')->setMethods(
            ['getRepository']
        )->disableOriginalConstructor()->getMock();

        return $em;
    }

}
