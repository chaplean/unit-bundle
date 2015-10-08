<?php

namespace Chaplean\Bundle\UnitBundle\Tests;

use Chaplean\Bundle\UnitBundle\Test\LogicalTest;

/**
 * HelperReferenceTest.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     2.0.0
 */
class HelperReferenceTest extends LogicalTest
{
    /**
     * @return void
     */
    public static function setUpBeforeClass()
    {
        self::loadFixtures(
            array(
                'Chaplean\Bundle\UnitBundle\DataFixtures\Liip\LoadProviderData'
            )
        );
    }

    /**
     * @return void
     */
    public function testFindClient()
    {
        $client = $this->getRealEntity('client-1');

        $this->assertEquals('Chaplean\Bundle\UnitBundle\Entity\Client', get_class($client));
    }
    /**
     * @return void
     */
    public function testFindProduct()
    {
        $product = $this->getRealEntity('product-1');

        $this->assertEquals('Chaplean\Bundle\UnitBundle\Entity\Product', get_class($product));
    }
    /**
     * @return void
     */
    public function testFindProvider()
    {
        $provider = $this->getRealEntity('provider-1');

        $this->assertEquals('Chaplean\Bundle\UnitBundle\Entity\Provider', get_class($provider));
    }
}
