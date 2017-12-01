<?php

namespace Chaplean\Bundle\UnitBundle\Model;

use Chaplean\Bundle\UnitBundle\TextUI\Output;
use Chaplean\Bundle\UnitBundle\Utility\Timer;

/**
 * Class TimerTest
 *
 * @package   Chaplean\Bundle\UnitBundle\Model
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2017 Chaplean (http://www.chaplean.com)
 * @since     7.0.0
 */
class TimerTest
{
    /** @var string */
    private $test;

    /** @var float */
    private $time;

    /**
     * TimerTest constructor.
     *
     * @param string $test
     * @param float  $time
     */
    public function __construct(string $test, float $time)
    {
        $this->test = $test;
        $this->time = $time;
    }

    public function __toString()
    {
        $time = $this->time * 1000;

        if ($time < 450) {
            $time = Output::success(Timer::toString($this->time));
        } elseif ($time >= 1000) {
            $time = Output::danger(Timer::toString($this->time));
        } else {
            $time = Output::warning(Timer::toString($this->time));
        }

        return sprintf("\t[%s] %s", str_pad($time, 16, ' ', STR_PAD_LEFT), $this->test);
    }
}