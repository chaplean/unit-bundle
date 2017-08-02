<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Test\LogicalTestCase;
use Chaplean\Bundle\UnitBundle\Utility\FixtureUtility;

/**
 * FixtureUtilityTest.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.coop)
 * @since     3.0.0
 */
class FixtureUtilityTest extends LogicalTestCase
{
    /**
     * @return void
     */
    public static function setUpBeforeClass()
    {
        self::loadStaticFixtures();
        parent::setUpBeforeClass();
    }

    /**
     * @backupStaticAttributes disabled
     * @return void
     */
    public function testGetInstance()
    {
        $this->assertInstanceOf(FixtureUtility::class, FixtureUtility::getInstance());
    }

    /**
     * @return void
     */
    public function testLoadDefaultFixturesWithNamespace()
    {
        $datas = FixtureUtility::getInstance()->loadDefaultFixtures();

        $this->assertTrue(in_array('Chaplean\Bundle\UnitBundle\DataFixtures\Liip\DefaultData\LoadStatusData', $datas));
        $this->assertTrue(in_array('Chaplean\Bundle\UnitBundle\DataFixtures\Liip\DefaultData\LoadClientData', $datas));
    }

    /**
     * @return void
     */
    public function testLoadPartialFixtures()
    {
        FixtureUtility::getInstance()->setContainer($this->getContainer());
        FixtureUtility::getInstance()->loadFixtures(array());
        FixtureUtility::getInstance()->loadPartialFixtures(array(
            'Chaplean\Bundle\UnitBundle\DataFixtures\Liip\DefaultData\LoadClientData',
            'Chaplean\Bundle\UnitBundle\DataFixtures\Liip\DefaultData\LoadStatusData',
        ), $this->em);

        $this->assertCount(1, $this->em->getRepository('ChapleanUnitBundle:Client')->findAll());
    }
}
