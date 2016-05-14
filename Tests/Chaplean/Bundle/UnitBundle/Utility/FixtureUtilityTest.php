<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Test\LogicalTest;
use Chaplean\Bundle\UnitBundle\Utility\FixtureUtility;

/**
 * FixtureUtilityTest.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.com)
 * @since     3.0.0
 */
class FixtureUtilityTest extends LogicalTest
{
    /**
     * @return void
     */
    public static function setUpBeforeClass()
    {
    }

    /**
     * @return void
     */
    public function setUp()
    {
    }

    /**
     * @return void
     */
    public function testLoadDefaultFixturesWithNamespace()
    {
        $datas = FixtureUtility::loadDefaultFixtures($this->getNamespace());

        $this->assertTrue(in_array('Chaplean\Bundle\UnitBundle\DataFixtures\Liip\DefaultData\LoadStatusData', $datas));
        $this->assertTrue(in_array('Chaplean\Bundle\UnitBundle\DataFixtures\Liip\DefaultData\LoadClientData', $datas));
    }

    /**
     * @return void
     */
    public function testLoadPartialFixturesWithoutEntityManager()
    {
        FixtureUtility::setContainer($this->getContainer());
        FixtureUtility::loadFixtures(array());
        FixtureUtility::loadPartialFixtures(array(
            'Chaplean\Bundle\UnitBundle\DataFixtures\Liip\DefaultData\LoadClientData',
            'Chaplean\Bundle\UnitBundle\DataFixtures\Liip\DefaultData\LoadStatusData',
        ), null);

        $this->assertCount(1, $this->em->getRepository('ChapleanUnitBundle:Client')->findAll());
    }
}
