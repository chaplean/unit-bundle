<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Test;

use Chaplean\Bundle\UnitBundle\Entity\Product;
use Chaplean\Bundle\UnitBundle\Test\LogicalTestCase;

/**
 * LoadByContextTest.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.coop)
 * @since     2.0.0
 */
class LoadByContextTest extends LogicalTestCase
{
    /**
     * @return void
     */
    public static function setUpBeforeClass()
    {
        self::markTestSkipped('');
        self::loadFixturesByContext('ForIrreleventDependencies');
        parent::setUpBeforeClass();
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
        $product = $this->getReference('product-injected');
        $this->assertEquals('Product injected by partial context', $product->getName());
    }
}
