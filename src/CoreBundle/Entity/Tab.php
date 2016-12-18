<?php

namespace CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="tabs")
 */
class Tab
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @ORM\Column(type="string")
     */
    protected $color;

    /**
     * @ORM\Column(type="integer")
     */
    protected $order;

    /**
     * @ORM\Column(type="string")
     */
    protected $icon;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="tabs")
     * @var User
     */
    protected $user;

    /**
     * @ORM\OneToMany(targetEntity="Achievement", mappedBy="tab")
     * @var Achievement[]
     */
    protected $achievements;

    function getId()
    {
        return $this->id;
    }

    function getName()
    {
        return $this->name;
    }

    function getUser()
    {
        return $this->user;
    }

    function setId($id)
    {
        $this->id = $id;
    }

    function setName($name)
    {
        $this->name = $name;
    }

    function setUser(User $user)
    {
        $this->user = $user;
    }

    function getColor()
    {
        return $this->color;
    }

    function getOrder()
    {
        return $this->order;
    }

    function getIcon()
    {
        return $this->icon;
    }

    function setColor($color)
    {
        $this->color = $color;
    }

    function setOrder($order)
    {
        $this->order = $order;
    }

    function setIcon($icon)
    {
        $this->icon = $icon;
    }

    function getAchievements()
    {
        return $this->achievements;
    }

    function setAchievements(array $achievements)
    {
        $this->achievements = $achievements;
    }
}
