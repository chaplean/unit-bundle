<?php

namespace Tests\Chaplean\Bundle\UnitBundle;

use Chaplean\Bundle\UnitBundle\Entity\Product;
use Chaplean\Bundle\UnitBundle\Test\LogicalTest;

/**
 * LoadByContextTest.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     2.0.0
 */
class LoadByContextTest extends LogicalTest
{
    /**
     * @return void
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::loadFixturesByContext('ForIrreleventDependencies');
    }

    /**
     * @return void
     */
    public function testProvider()
    {
        $this->assertCount(1, $this->em->getRepository('ChapleanUnitBundle:Provider')->findAll());
        $this->assertCount(2, $this->em->getRepository('ChapleanUnitBundle:Product')->findAll());
        $this->assertCount(2, $this->em->getRepository('ChapleanUnitBundle:Client')->findAll());
    }

    /**
     * @return void
     */
    public function testPartialFixturesByContext()
    {
        $this->loadPartialFixturesByContext('PartialContext');

        $this->assertCount(3, $this->em->getRepository('ChapleanUnitBundle:Product')->findAll());
        $this->assertCount(1, $this->em->getRepository('ChapleanUnitBundle:Provider')->findAll());

        $this->assertInstanceOf(Product::class, self::$fixtures->getReference('product-injected'));

        /** @var Product $product */
        $product = $this->getEntity('product-injected');
        $this->assertEquals('Product injected by partial context', $product->getName());
    }
}
