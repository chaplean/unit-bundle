<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Test;

use Chaplean\Bundle\UnitBundle\Entity\Client;
use Chaplean\Bundle\UnitBundle\Entity\Product;
use Chaplean\Bundle\UnitBundle\Entity\Provider;
use Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase;

/**
 * HelperReferenceTest.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.coop)
 * @since     2.0.0
 */
class HelperReferenceTest extends FunctionalTestCase
{
    /**
     * @return void
     */
    public function testFindClient()
    {
        /** @var Client $client */
        $client = $this->getReference('client-1');

        $this->assertInstanceOf(Client::class, $client);
    }

    /**
     * @return void
     */
    public function testFindProduct()
    {
        $product = $this->getReference('product-1');

        $this->assertInstanceOf(Product::class, $product);
    }

    /**
     * @return void
     */
    public function testFindProvider()
    {
        $provider = $this->getReference('provider-1');

        $this->assertInstanceOf(Provider::class, $provider);
    }
}
