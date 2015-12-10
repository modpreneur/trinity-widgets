<?php


namespace Entity;


use Trinity\FrameworkBundle\Entity\BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * User.
 *
 * @ORM\Table(name="user")
 * @ORM\HasLifecycleCallbacks
 */
class User extends BaseUser
{

}