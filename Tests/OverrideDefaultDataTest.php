<?php

namespace Tests\Chaplean\Bundle\UnitBundle;

use Chaplean\Bundle\UnitBundle\Test\LogicalTest;

/**
 * OverrideDefaultData.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     2.0.0
 */
class OverrideDefaultDataTest extends LogicalTest
{
    /**
     * @return void
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::loadStaticFixtures(array(
            'Chaplean\Bundle\UnitBundle\DataFixtures\Liip\LoadClientData'
        ));
    }

    /**
     * @return void
     */
    public function testCountClient()
    {
        $this->assertCount(1, $this->em->getRepository('ChapleanUnitBundle:Client')->findAll());
    }
}
