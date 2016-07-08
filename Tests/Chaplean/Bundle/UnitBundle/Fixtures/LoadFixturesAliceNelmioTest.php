<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Fixtures;

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
    public static function setUpBeforeClass()
    {
        self::loadStaticFixtures(array(
            'Chaplean\Bundle\UnitBundle\DataFixtures\Liip\LoadFixturesData'
        ));

        parent::setUpBeforeClass();
    }

    /**
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
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
