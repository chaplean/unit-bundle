<?php

namespace Chaplean\Bundle\UnitBundle\DataFixtures\Liip\PartialContext;

use Chaplean\Bundle\UnitBundle\Entity\Product;
use Chaplean\Bundle\UnitBundle\Utility\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * LoadProductData.php.
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
        $product = new Product();

        $product->setName('Product injected by partial context');

        $this->persist($product, $manager);
        $this->setReference('product-injected', $product);

        $manager->flush();
    }
}
