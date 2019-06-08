<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Utility\FixtureLiteUtility;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Tests\Chaplean\Bundle\UnitBundle\Functional\FunctionalTestCase;

/**
 * Class FixtureLiteUtilityTest.
 *
 * @package             Tests\Chaplean\Bundle\UnitBundle\Utility
 * @author              Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright           2014 - 2017 Chaplean (https://www.chaplean.coop)
 * @since               7.0.0
 */
class FixtureLiteUtilityTest extends FunctionalTestCase
{
    use MockeryPHPUnitIntegration;

    /**
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
     * @throws \Exception
     *
     * @doesNotPerformAssertions
     */
    public function testLoadClass()
    {
        $fixture = FixtureLiteUtility::getInstance(static::$container);

        $fixture->loadFixtures([]);
    }
}
