<?php

namespace Chaplean\Bundle\UnitBundle\DataFixtures;

use Doctrine\Common\DataFixtures\ProxyReferenceRepository as BaseProxyReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class ProxyReferenceRepository.
 *
 * @package   Chaplean\Bundle\UnitBundle\DataFixtures
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2019 Chaplean (https://www.chaplean.coop)
 */
class ProxyReferenceRepository extends BaseProxyReferenceRepository
{
    /**
     * @see BaseProxyReferenceRepository::getReference()
     *
     * @param string                 $name
     * @param EntityManagerInterface $manager
     *
     * @return object
     * @throws \Doctrine\ORM\ORMException
     */
    public function getReferenceWithManager(string $name, ?EntityManagerInterface $manager = null)
    {
        /** @var EntityManagerInterface $manager */
        $manager = ($manager !== null) ? $manager : $this->getManager();

        if (!$this->hasReference($name)) {
            throw new \OutOfBoundsException("Reference to: ({$name}) does not exist");
        }

        $reference = $this->getReferences()[$name];
        $meta = $manager->getClassMetadata(get_class($reference));

        if (!$manager->contains($reference) && isset($this->getIdentities()[$name])) {
            $reference = $manager->getReference(
                $meta->name,
                $this->getIdentities()[$name]
            );
            $this->getReferences()[$name] = $reference; // already in identity map
        }

        return $reference;
    }
}
