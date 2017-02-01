<?php

namespace CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="achievements")
 *
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\AchievementRepository")
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
     * @ORM\Column(type="integer")
     */
    protected $orderNumber;

    /**
     * @ORM\Column(type="string", nullable=true)
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

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $lastOrderNumberModification;

    function defaultValues($orderNumberHasChanged)
    {
        if ($this->getOrderNumber() == null) {
            $this->setOrderNumber(0);
        }
        if ($this->getFavorite() == null) {
            $this->setFavorite(false);
        }
        if ($this->getShortdesc() == null) {
            $this->setShortdesc("Short description of the achievement");
        }
        if ($this->getLongdesc() == null) {
            $this->setLongdesc("Complete description of the achievement");
        }
        if ($orderNumberHasChanged) {
            $this->lastOrderNumberModification();
        }
        $this->getTab()->getUser()->calculNbAchievements();
    }

    public function lastOrderNumberModification()
    {
        $this->setLastOrderNumberModification(date('Y-m-d H:i:s'));
    }

    function getId()
    {
        return $this->id;
    }

    function getName()
    {
        return $this->name;
    }

    function getOrderNumber()
    {
        return $this->orderNumber;
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

    function setOrderNumber($order)
    {
        $this->orderNumber = $order;
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

    function getLastOrderNumberModification()
    {
        return $this->lastOrderNumberModification;
    }

    function setLastOrderNumberModification($lastOrderNumberModification)
    {
        $this->lastOrderNumberModification = $lastOrderNumberModification;
    }
}
