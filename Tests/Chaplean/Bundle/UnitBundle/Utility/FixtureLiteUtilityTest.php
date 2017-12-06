<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Utility\FixtureLiteUtility;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
     * @runTestsInSeparateProcesses
     *
     * @covers \Chaplean\Bundle\UnitBundle\Utility\FixtureLiteUtility::getInstance()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\FixtureLiteUtility::setContainer()
     *
     * @return void
     */
    public function testGetInstance()
    {
        /** @var Container|MockInterface $container */
        $container = \Mockery::mock(ContainerInterface::class);

        $instance1 = FixtureLiteUtility::getInstance($container);

        $this->assertInstanceOf(FixtureLiteUtility::class, $instance1);

        $instance2 = FixtureLiteUtility::getInstance($container);

        $this->assertEquals(spl_object_hash($instance1), spl_object_hash($instance2));
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\FixtureLiteUtility::loadFixtures()
     *
     * @return void
     */
    public function testLoadClass()
    {
        /** @var Container|MockInterface $container */
        $container = \Mockery::mock(ContainerInterface::class);

        $fixture = FixtureLiteUtility::getInstance($container);

        $fixture->loadFixtures([]);

        $this->assertTrue(true);
    }
}
