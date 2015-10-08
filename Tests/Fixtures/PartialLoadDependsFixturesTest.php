<?php

namespace Chaplean\Bundle\UnitBundle\Tests\Fixtures;

use Chaplean\Bundle\UnitBundle\Test\LogicalTest;

/**
 * PartialLoadDependsFixturesTest.php.
 *
 * @author    Matthias - Chaplean <matthias@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     2.0.0
 */
class PartialLoadDependsFixturesTest extends LogicalTest
{
    /**
     * @return void
     */
    public static function setUpBeforeClass()
    {
        self::loadFixtures(array('Chaplean\Bundle\UnitBundle\DataFixtures\Liip\LoadProductData'));
    }

    /**
     * @return void
     */
    public function testPartialFixtureWithDepends()
    {
        $this->assertCount(1, $this->em->getRepository('ChapleanUnitBundle:Client')->findAll());
        $this->assertCount(0, $this->em->getRepository('ChapleanUnitBundle:Provider')->findAll());

        $this->loadPartialFixtures(array('Chaplean\Bundle\UnitBundle\DataFixtures\Liip\LoadProviderData'));

        $this->assertCount(1, $this->em->getRepository('ChapleanUnitBundle:Client')->findAll());
        $this->assertCount(1, $this->em->getRepository('ChapleanUnitBundle:Provider')->findAll());
    }
}