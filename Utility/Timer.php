<?php

namespace Chaplean\Bundle\UnitBundle\Utility;

/**
 * Class Time
 *
 * @package Chaplean\Bundle\UnitBundle\Utility
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2017 Chaplean (http://www.chaplean.com)
 * @since     3.0.0
 */
class Timer
{
    private static $time;

    /**
     * Start a timer
     *
     * @return float
     */
    public static function start()
    {
        self::$time = microtime(true);

        return self::$time;
    }

    /**
     * Stop a timer and return the elapsed time
     *
     * @return float
     * @throws \Exception
     */
    public static function stop()
    {
        if (self::$time === null) {
            throw new \Exception('The timer is not started');
        }

        $elapsedTime = microtime(true) - self::$time;

        self::$time = null;

        return $elapsedTime;
    }

    /**
     * @param float $time
     *
     * @return string
     */
    public static function toString(float $time)
    {
        if ($time >= 1) {
            return sprintf('%.2f', $time) . 's';
        } else {
            return sprintf('%d', $time * 1000) . 'ms';
        }
    }
}