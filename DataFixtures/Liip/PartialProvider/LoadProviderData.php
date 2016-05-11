<?php

namespace Chaplean\Bundle\UnitBundle\DataFixtures\Liip\PartialProvider;

use Chaplean\Bundle\UnitBundle\Entity\Provider;
use Chaplean\Bundle\UnitBundle\Utility\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * LoadClientData.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     3.0.0
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
        $provider = new Provider();

        $provider->setName('Stylo');
        $provider->setProduct($this->getEntity('product-1', $manager));

        $this->persist($provider, $manager);
        $this->setReference('provider-1', $provider);

        $manager->flush();
    }
}
