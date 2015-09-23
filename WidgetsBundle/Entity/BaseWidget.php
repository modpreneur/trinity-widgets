<?php
namespace Trinity\WidgetsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * Class Widget
 * @package Trinity\WidgetsBundle\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="widget")
 *
 */
class BaseWidget
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;


    /**
     * @var string
     *
     * Widget name
     *
     * @ORM\Column(type="string")
     */
    private $name;


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

}