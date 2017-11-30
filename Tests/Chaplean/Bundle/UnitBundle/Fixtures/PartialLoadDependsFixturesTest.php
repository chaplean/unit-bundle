<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Fixtures;

use Chaplean\Bundle\UnitBundle\Test\LogicalTestCase;

/**
 * PartialLoadDependsFixturesTest.php.
 *
 * @author    Matthias - Chaplean <matthias@chaplean.coop>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.coop)
 * @since     2.0.0
 */
class PartialLoadDependsFixturesTest extends LogicalTestCase
{
    /**
     * @return void
     */
    public static function setUpBeforeClass()
    {
        self::markTestSkipped('S');
        self::loadStaticFixtures(array('Chaplean\Bundle\UnitBundle\DataFixtures\Liip\LoadProductData'));

        parent::setUpBeforeClass();
    }

    /**
     * @return void
     */
    public function testPartialFixtureWithDepends()
    {
        $this->assertCount(1, $this->em->getRepository('ChapleanUnitBundle:Client')->findAll());
        $this->assertCount(1, $this->em->getRepository('ChapleanUnitBundle:Product')->findAll());
        $this->assertCount(0, $this->em->getRepository('ChapleanUnitBundle:Provider')->findAll());

        $this->loadPartialFixtures(array('Chaplean\Bundle\UnitBundle\DataFixtures\Liip\PartialProvider\LoadProviderData'));

        $this->assertCount(1, $this->em->getRepository('ChapleanUnitBundle:Client')->findAll());
        $this->assertCount(1, $this->em->getRepository('ChapleanUnitBundle:Product')->findAll());
        $this->assertCount(1, $this->em->getRepository('ChapleanUnitBundle:Provider')->findAll());
    }
}
