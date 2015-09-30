<?php
/**
 * PartialLoadDependsFixturesTest.php.
 *
 * @author    Matthias - Chaplean <matthias@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     2.0.0
 */

namespace Chaplean\Bundle\UnitBundle\Tests\Fixtures;

use Chaplean\Bundle\UnitBundle\Test\LogicalTest;

class PartialLoadDependsFixturesTest extends LogicalTest
{
    public static function setUpBeforeClass()
    {
        self::loadStaticFixtures(array('Chaplean\Bundle\UnitBundle\DataFixtures\Liip\LoadProductData'));
    }

    public function testPartialFixtureWithDepends()
    {
        $this->assertCount(1, $this->em->getRepository('ChapleanUnitBundle:Client')->findAll());
        $this->loadPartialFixtures(array('Chaplean\Bundle\UnitBundle\DataFixtures\Liip\LoadProviderData'));
        $this->assertCount(1, $this->em->getRepository('ChapleanUnitBundle:Client')->findAll());
    }
}