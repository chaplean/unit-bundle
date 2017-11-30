<?php

namespace Chaplean\Bundle\UnitBundle\Utility;

/**
 * Class Time
 * @package Chaplean\Bundle\UnitBundle\Utility
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2017 Chaplean (http://www.chaplean.com)
 * @since     X.Y.Z
 */
class Time
{
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