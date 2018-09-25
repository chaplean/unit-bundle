<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Utility\Timer;
use PHPUnit\Framework\TestCase;

/**
 * Class TimeTest.
 *
 * @package   Tests\Chaplean\Bundle\UnitBundle\Utility
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2017 Chaplean (http://www.chaplean.coop)
 * @since     7.0.0
 */
class TimerTest extends TestCase
{
    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Timer::toString()
     *
     * @return void
     */
    public function testToString()
    {
        $this->assertEquals('1.25s', Timer::toString(1.245694));
        $this->assertEquals('300ms', Timer::toString(0.30057898));
        $this->assertEquals('3ms', Timer::toString(0.003057898));
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Timer::stop()
     *
     * @return void
     *
     * @expectedException \Exception
     * @expectedExceptionMessage The timer is not started
     */
    public function testStopWithoutTimerStarted()
    {
        Timer::stop();
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Timer::start()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Timer::stop()
     *
     * @return void
     */
    public function testStartStop()
    {
        Timer::start();

        $this->assertGreaterThan(0.0, Timer::stop());
    }
}
