<?php

namespace Tests\Chaplean\Bundle\UnitBundle;

use Chaplean\Bundle\UnitBundle\Entity\Client;
use Chaplean\Bundle\UnitBundle\Entity\Product;
use Chaplean\Bundle\UnitBundle\Entity\Provider;
use Chaplean\Bundle\UnitBundle\Test\LogicalTestCase;
use Doctrine\ORM\Query;

/**
 * HelperReferenceTest.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     2.0.0
 */
class HelperReferenceTest extends LogicalTestCase
{
    /**
     * @return void
     */
    public static function setUpBeforeClass()
    {
        self::loadStaticFixtures(
            array(
                'Chaplean\Bundle\UnitBundle\DataFixtures\Liip\LoadProviderData'
            )
        );

        parent::setUpBeforeClass();
    }

    /**
     * @return void
     */
    public function testFindClient()
    {
        /** @var Client $client */
        $client = $this->getEntity('client-1');

        $this->assertInstanceOf(Client::class, $client);
    }

    /**
     * @return void
     */
    public function testFindProduct()
    {
        $product = $this->getEntity('product-1');

        $this->assertInstanceOf(Product::class, $product);
    }

    /**
     * @return void
     */
    public function testFindProvider()
    {
        $provider = $this->getEntity('provider-1');

        $this->assertInstanceOf(Provider::class, $provider);
    }
}
