<?php
namespace Chaplean\Bundle\UnitBundle\Utility;

use Doctrine\Common\DataFixtures\AbstractFixture as BaseAbstractFixture;
use Doctrine\Entity;
use Doctrine\ORM\EntityManager;

/**
 * AbstractFixture.php.
 *
 * @author    Matthias - Chaplean <matthias@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     2.0.0
 */
abstract class AbstractFixture extends BaseAbstractFixture
{
    /**
     * Loads an Entity using stored reference
     * named by $name
     *
     * @param string        $name
     * @param EntityManager $manager
     *
     * @return Entity
     */
    public function getEntity($name, $manager)
    {
        $entity = $this->referenceRepository->getReference($name);
        return $manager->find(get_class($entity), $entity->getId());
    }
}