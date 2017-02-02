<?php

namespace CoreBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity()
 * @ORM\Table(name="users", uniqueConstraints={@ORM\UniqueConstraint(name="users_email_unique",columns={"email"})})
 *
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\UserRepository")
 */
class User implements UserInterface
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
    protected $firstname;

    /**
     * @ORM\Column(type="string")
     */
    protected $lastname;

    /**
     * @ORM\Column(type="string")
     */
    protected $email;

    /**
     * @ORM\Column(type="integer")
     */
    protected $nbAchievements;

    /**
     * @ORM\Column(type="string")
     */
    protected $password;
    protected $plainPassword;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var File
     */
    protected $profilePicture;

    /**
     * @ORM\OneToMany(targetEntity="Tab", mappedBy="user", cascade={"persist", "remove"})
     * @ORM\OrderBy({"orderNumber" = "ASC"})
     * @var Tab[]
     */
    protected $tabs;

    function getProfilePicture()
    {
        return $this->profilePicture;
    }

    function setProfilePicture($profilePicture)
    {
        $this->profilePicture = $profilePicture;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getRoles()
    {
        return [];
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername()
    {
        return $this->email;
    }

    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    public function __construct()
    {
        $this->tabs = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getFirstname()
    {
        return $this->firstname;
    }

    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    public function getLastname()
    {
        return $this->lastname;
    }

    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    function getTabs()
    {
        return $this->tabs;
    }

    function setTabs(array $tabs)
    {
        $this->tabs = $tabs;
    }

    function getPlainPassword()
    {
        return $this->plainPassword;
    }

    function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;
    }

    function getNbAchievements()
    {
        return $this->nbAchievements;
    }

    function setNbAchievements($nbAchievements)
    {
        $this->nbAchievements = $nbAchievements;
    }

    public function calculNbAchievements()
    {
        $total = 0;
        foreach ($this->getTabs() as $t) {
            $total += $t->getAchievements()->count();
        }
        $this->setNbAchievements($total);
    }
}
