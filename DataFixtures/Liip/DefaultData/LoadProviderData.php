<?php

namespace Chaplean\Bundle\UnitBundle\DataFixtures\Liip\DefaultData;

use Chaplean\Bundle\UnitBundle\Entity\Product;
use Chaplean\Bundle\UnitBundle\Entity\Provider;
use Chaplean\Bundle\UnitBundle\Utility\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * LoadClientData.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2015 Chaplean (https://www.chaplean.coop)
 * @since     2.0.0
 */
class LoadProviderData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * @param ObjectManager $manager
     *
     * @return void
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        /** @var Product $product1 */
        $product1 = $this->getReference('product-1');
        $provider = new Provider();

        $provider->setName('Stylo');
        $provider->setProduct($product1);

        $manager->persist($provider);
        $this->setReference('provider-1', $provider);

        $manager->flush();
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on
     *
     * @return array
     */
    public function getDependencies()
    {
        return [
            LoadProductData::class
        ];
    }
}
