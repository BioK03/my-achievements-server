<?php

namespace CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="achievements")
 */
class Achievement
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
     * @ORM\Column(type="string")
     */
    protected $shortdesc;

    /**
     * @ORM\Column(type="text")
     */
    protected $longdesc;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $favorite;

    /**
     * @ORM\ManyToOne(targetEntity="Tab", inversedBy="achievements")
     * @var Tab
     */
    protected $tab;

    function getId()
    {
        return $this->id;
    }

    function getName()
    {
        return $this->name;
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

    function getShortdesc()
    {
        return $this->shortdesc;
    }

    function getLongdesc()
    {
        return $this->longdesc;
    }

    function getFavorite()
    {
        return $this->favorite;
    }

    function getTab()
    {
        return $this->tab;
    }

    function setId($id)
    {
        $this->id = $id;
    }

    function setName($name)
    {
        $this->name = $name;
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

    function setShortdesc($shortdesc)
    {
        $this->shortdesc = $shortdesc;
    }

    function setLongdesc($longdesc)
    {
        $this->longdesc = $longdesc;
    }

    function setFavorite($favorite)
    {
        $this->favorite = $favorite;
    }

    function setTab(Tab $tab)
    {
        $this->tab = $tab;
    }
}
