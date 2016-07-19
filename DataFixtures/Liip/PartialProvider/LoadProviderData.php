<?php

namespace Chaplean\Bundle\UnitBundle\DataFixtures\Liip\PartialProvider;

use Chaplean\Bundle\UnitBundle\Entity\Product;
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
        /** @var Product $product1 */
        $product1 = $this->getReference('product-1');
        $provider = new Provider();

        $provider->setName('Stylo');
        $provider->setProduct($product1);

        $this->persist($provider, $manager);
        $this->setReference('provider-1', $provider);

        $manager->flush();
    }
}
