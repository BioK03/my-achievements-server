<?php

namespace SM\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

class FileRepository extends EntityRepository
{

    public function isNotUsed($path)
    {
        $q = $this->getEntityManager()->createQuery("SELECT COUNT(f) FROM CoreBundle:File f WHERE (f.path= :path)")->setParameter('path', $path);
        $r = $q->getOneOrNullResult();
        return (($r != null) ? $r[1] <= 0 : true);
    }
}
