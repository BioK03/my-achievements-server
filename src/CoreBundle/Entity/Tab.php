<?php

namespace CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Criteria;

/**
 * @ORM\Entity()
 * @ORM\Table(name="tabs")
 *
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\TabRepository")
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
     * @ORM\Column(type="string", nullable=true)
     */
    protected $color;

    /**
     * @ORM\Column(type="integer")
     */
    protected $orderNumber;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $icon;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="tabs")
     * @var User
     */
    protected $user;

    /**
     * @ORM\OneToMany(targetEntity="Achievement", mappedBy="tab", cascade={"persist", "remove"})
     * @ORM\OrderBy({"orderNumber" = "ASC"})
     * @var Achievement[]
     */
    protected $achievements;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $lastOrderNumberModification;

    function defaultValues($orderNumberHasChanged)
    {
        if ($this->getOrderNumber() == null) {
            $this->setOrderNumber(1);
        }
        if ($orderNumberHasChanged) {
            $this->lastOrderNumberModification();
        } $this->getUser()->calculNbAchievements();
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

    function getOrderNumber()
    {
        return $this->orderNumber;
    }

    function getIcon()
    {
        return $this->icon;
    }

    function setColor($color)
    {
        $this->color = $color;
    }

    function setOrderNumber($order)
    {
        $this->orderNumber = $order;
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

    function getLastOrderNumberModification()
    {
        return $this->lastOrderNumberModification;
    }

    function setLastOrderNumberModification($lastOrderNumberModification)
    {
        $this->lastOrderNumberModification = $lastOrderNumberModification;
    }
}
