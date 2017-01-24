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
        var_dump($query);
        return $this->getEntityManager()->createQuery($query)->getResult();
    }
}
