<?php

namespace Chaplean\Bundle\UnitBundle\Tests\Fixtures;

use Chaplean\Bundle\UnitBundle\Test\LogicalTest;

/**
 * LoadFixturesAliceNelmioTest.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     2.0.0
 */
class LoadFixturesAliceNelmioTest extends LogicalTest
{
    /**
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        self::loadStaticFixtures(array(
            'Chaplean\Bundle\UnitBundle\DataFixtures\Liip\LoadFixturesData'
        ));
    }

    /**
     * @return void
     */
    public function testClient()
    {
        $clients = $this->em->getRepository('ChapleanUnitBundle:Client')->findAll();

        $this->assertCount(10, $clients);
    }
}
