<?php

namespace Chaplean\Bundle\UnitBundle\DataFixtures\Liip\LoadPartial;

use Chaplean\Bundle\UnitBundle\Entity\Client;
use Chaplean\Bundle\UnitBundle\Entity\Product;
use Chaplean\Bundle\UnitBundle\Utility\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * LoadClientData.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     2.0.0
 */
class LoadProductData extends AbstractFixture
{
    /**
     * @param ObjectManager $manager
     *
     * @return void
     */
    public function load(ObjectManager $manager)
    {
        /** @var Client $client1 */
        $client1 = $this->getReference('client-1');

        $product = new Product();

        $product->setName('Stylo');
        $product->setClient($client1);

        $this->persist($product, $manager);
        $this->setReference('product-1', $product);

        $manager->flush();
    }
}