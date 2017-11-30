<?php

namespace Chaplean\Bundle\UnitBundle\Model;
use Chaplean\Bundle\UnitBundle\Utility\Output;
use Chaplean\Bundle\UnitBundle\Utility\Time;

/**
 * Class TimerTest
 * @package Chaplean\Bundle\UnitBundle\Model
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2017 Chaplean (http://www.chaplean.com)
 * @since     6.0.0
 */
class TimerTest
{
    private $test;
    private $time;

    public function __construct($test, $time)
    {
        $this->test = $test;
        $this->time = $time;
    }

    public function __toString()
    {
        $time = $this->time * 1000;

        if ($time < 450) {
            $time = Output::success(Time::toString($this->time));
        } elseif ($time >= 1000) {
            $time = Output::danger(Time::toString($this->time));
        } else {
            $time = Output::warning(Time::toString($this->time));
        }

        return sprintf("\t%s: %s", $this->test, $time);
    }
}