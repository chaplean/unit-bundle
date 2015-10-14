<?php

namespace Chaplean\Bundle\UnitBundle\DataFixtures\Liip\ForIrreleventDependencies;

use Chaplean\Bundle\UnitBundle\Entity\Type;
use Chaplean\Bundle\UnitBundle\Utility\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * LoadTypeData.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     2.0.0
 */
class LoadTypeData extends AbstractFixture
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
        $type = new Type();

        $this->persist($type, $manager);

        $manager->flush();
    }
}
