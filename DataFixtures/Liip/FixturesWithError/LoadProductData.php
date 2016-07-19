<?php

namespace Chaplean\Bundle\UnitBundle\DataFixtures\Liip\FixturesWithError;

use Chaplean\Bundle\UnitBundle\Entity\Product;
use Chaplean\Bundle\UnitBundle\Utility\AbstractFixture;
use Chaplean\Bundle\UnitBundle\Utility\Client;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * LoadClientData.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     2.0.0
 */
class LoadProductData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * @param ObjectManager $manager
     *
     * @return void
     */
    public function load(ObjectManager $manager)
    {
        /** @var Client $client2 */
        $client2 = $this->getReference('client-2');
        $product = new Product();

        $product->setName('Stylo');
        $product->setClient($client2);

        $this->persist($product, $manager);
        $this->setReference('product-1', $product);

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
        return array('Chaplean\Bundle\UnitBundle\DataFixtures\Liip\FixturesWithError\LoadClientData');
    }
}