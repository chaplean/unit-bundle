<?php

namespace Chaplean\Bundle\UnitBundle\DataFixtures\Liip;

use Chaplean\Bundle\UnitBundle\Utility\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Nelmio\Alice\Fixtures;

/**
 * LoadFixturesData.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     2.0.0
 */
class LoadFixturesData extends AbstractFixture
{

    /**
     * @param ObjectManager $manager
     *
     * @return void
     */
    public function load(ObjectManager $manager)
    {
        Fixtures::load(__DIR__ . '/fixtures.yml', $manager);
    }
}
