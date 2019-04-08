<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Utility\FixtureLiteUtility;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class FixtureLiteUtilityTest.
 *
 * @package   Tests\Chaplean\Bundle\UnitBundle\Utility
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2017 Chaplean (https://www.chaplean.coop)
 * @since     7.0.0
 */
class FixtureLiteUtilityTest extends TestCase
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
     * @covers \Chaplean\Bundle\UnitBundle\Utility\FixtureLiteUtility
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
