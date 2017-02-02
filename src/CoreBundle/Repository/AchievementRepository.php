<?php

namespace CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

class AchievementRepository extends EntityRepository
{

    public function getAchievementsAsc($tab_id)
    {
        $query = "SELECT a FROM CoreBundle:Achievement a where a.tab =".$tab_id." ORDER BY a.orderNumber, a.lastOrderNumberModification ASC";
        return $this->getEntityManager()->createQuery($query)->getResult();
    }

    public function getAchievementsDesc($tab_id)
    {
        $query = "SELECT a FROM CoreBundle:Achievement a where a.tab =".$tab_id." ORDER BY a.orderNumber, a.lastOrderNumberModification DESC";
        return $this->getEntityManager()->createQuery($query)->getResult();
    }

    public function getAllFiles()
    {
        $query = "SELECT a.images FROM CoreBundle:Achievement a ";
        return $this->getEntityManager()->createQuery($query)->getResult();
    }
}
