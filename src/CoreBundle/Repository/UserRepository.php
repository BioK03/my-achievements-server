<?php

namespace CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{

    public function search($words)
    {
        $query = "";
        foreach ($words as $w) {
            if ($query == null) {
                $query .= "SELECT u FROM CoreBundle:User u where ";
            } else {
                $query .= "OR ";
            }
            $query .= "u.firstname LIKE '%".$w."%' OR u.lastname LIKE '%".$w."%'";
        }
        return $this->getEntityManager()->createQuery($query)->getResult();
    }

    public function getAllFiles()
    {
        $query = "SELECT u.profilePicture FROM CoreBundle:User u ";
        return $this->getEntityManager()->createQuery($query)->getResult();
    }
}
