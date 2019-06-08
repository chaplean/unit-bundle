<?php

namespace Chaplean\Bundle\UnitBundle\Utility;

use Doctrine\Common\DataFixtures\AbstractFixture as BaseAbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * AbstractFixture.php.
 *
 * @author    Matthias - Chaplean <matthias@chaplean.coop>
 * @copyright 2014 - 2015 Chaplean (https://www.chaplean.coop)
 * @since     2.0.0
 */
abstract class AbstractFixture extends BaseAbstractFixture
{
    /**
     * Loads an object using stored reference named by $name
     *
     * @param string $name
     *
     * @return object
     * @throws \Exception
     * @see \Doctrine\Common\DataFixtures\ReferenceRepository::getReference
     */
    public function getReference($name)
    {
        $reference = parent::getReference($name);

        if ($reference->getId() === null) {
            throw new \Exception(\sprintf('\'%s\' is not persisted !', $name));
        }

        return $reference;
    }

    /**
     * Persist a entity with random data for
     * required field not set
     *
     * @param mixed         $entity
     * @param ObjectManager $manager
     *
     * @return void
     * @deprecated Use $manager->persist() instead
     */
    public function persist($entity, ObjectManager $manager = null): void
    {
        $manager->persist($entity);
    }
}
