<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Utility\FixtureLiteUtility;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

/**
 * Class FixtureLiteUtilityTest.
 *
 * @package   Tests\Chaplean\Bundle\UnitBundle\Utility
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2017 Chaplean (http://www.chaplean.coop)
 * @since     7.0.0
 */
class FixtureLiteUtilityTest extends WebTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @runInSeparateProcess
     *
     * @covers \Chaplean\Bundle\UnitBundle\Utility\FixtureLiteUtility::getInstance()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\FixtureLiteUtility::setContainer()
     *
     * @return void
     */
    public function testGetInstance()
    {
        $instance1 = FixtureLiteUtility::getInstance($this->getContainer());

        $this->assertInstanceOf(FixtureLiteUtility::class, $instance1);

        $instance2 = FixtureLiteUtility::getInstance($this->getContainer());

        $this->assertEquals(spl_object_hash($instance1), spl_object_hash($instance2));
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\FixtureLiteUtility::loadFixtures()
     *
     * @return void
     */
    public function testLoadClass()
    {
        $fixture = FixtureLiteUtility::getInstance($this->getContainer());

        $fixture->loadFixtures([]);

        $this->assertTrue(true);
    }
}
