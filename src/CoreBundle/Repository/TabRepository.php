<?php

namespace CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

class TabRepository extends EntityRepository
{

    public function getTabsAsc($user_id)
    {
        $query = "SELECT t FROM CoreBundle:Tab t where t.user =".$user_id." ORDER BY t.orderNumber, t.lastOrderNumberModification ASC";
        return $this->getEntityManager()->createQuery($query)->getResult();
    }

    public function getTabsDesc($user_id)
    {
        $query = "SELECT t FROM CoreBundle:Tab t where t.user =".$user_id." ORDER BY t.orderNumber, t.lastOrderNumberModification DESC";
        return $this->getEntityManager()->createQuery($query)->getResult();
    }
}
