<?php

namespace Chaplean\Bundle\UnitBundle\DataFixtures\Liip\UserIrreleventWithReference;

use Chaplean\Bundle\UnitBundle\Entity\User;
use Chaplean\Bundle\UnitBundle\Utility\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * LoadUserData.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     2.0.0
 */
class LoadUserData extends AbstractFixture
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
        for ($i = 0; $i < 7; $i++) {
            $user = new User();
            $this->persist($user, $manager);
            $this->setReference('user-' . $i, $user);
        }

        $manager->flush();
    }
}
