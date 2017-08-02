<?php

namespace Chaplean\Bundle\UnitBundle\DataFixtures\Liip\ForIrreleventDependencies;

use Chaplean\Bundle\UnitBundle\Entity\Provider;
use Chaplean\Bundle\UnitBundle\Utility\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * LoadProductData.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.coop)
 * @since     2.0.0
 */
class LoadProviderData extends AbstractFixture
{
    /**
     * @param ObjectManager $manager
     *
     * @return void
     */
    public function load(ObjectManager $manager)
    {
        $product = new Provider();

        $this->persist($product, $manager);
        $this->setReference('provider-1', $product);

        $manager->flush();
    }
}
