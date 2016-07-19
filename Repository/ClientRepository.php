<?php

namespace Chaplean\Bundle\UnitBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class ClientRepository.
 *
 * @author    Tom - Chaplean <tom@chaplean.com>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.com)
 * @since     4.0.0
 */
class ClientRepository extends EntityRepository
{
    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getManager()
    {
        return $this->getEntityManager();
    }
}
