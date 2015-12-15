<?php

namespace Chaplean\Bundle\UnitBundle\DataFixtures\Liip\ForIrreleventDependencies;

use Chaplean\Bundle\UnitBundle\Entity\Enum;
use Chaplean\Bundle\UnitBundle\Utility\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * LoadEnumData.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     2.0.0
 */
class LoadEnumData extends AbstractFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     *
     * @return void
     */
    public function load(ObjectManager $manager)
    {
        $type = new Enum();

        $this->persist($type, $manager);

        $manager->flush();
    }
}
