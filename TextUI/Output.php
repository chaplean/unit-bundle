<?php

namespace Chaplean\Bundle\UnitBundle\TextUI;

/**
 * Class Output
 * @package Chaplean\Bundle\UnitBundle\Utility
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2017 Chaplean (http://www.chaplean.com)
 * @since     X.Y.Z
 */
class Output
{
    const COLOR_NC = "\033[0m"; # No Color
    const COLOR_WHITE = "\033[1;37m";
    const COLOR_BLACK = "\033[0;30m";
    const COLOR_BLUE = "\033[0;34m";
    const COLOR_LIGHT_BLUE = "\033[1;34m";
    const COLOR_GREEN = "\033[0;32m";
    const COLOR_LIGHT_GREEN = "\033[1;32m";
    const COLOR_CYAN = "\033[0;36m";
    const COLOR_LIGHT_CYAN = "\033[1;36m";
    const COLOR_RED = "\033[0;31m";
    const COLOR_LIGHT_RED = "\033[1;31m";
    const COLOR_PURPLE = "\033[0;35m";
    const COLOR_LIGHT_PURPLE = "\033[1;35m";
    const COLOR_BROWN = "\033[0;33m";
    const COLOR_YELLOW = "\033[1;33m";
    const COLOR_GRAY = "\033[0;30m";
    const COLOR_LIGHT_GRAY = "\033[0;37m";

    const CHAR_CHECK = '✓';

    /**
     * @param string $color
     * @param string $message
     *
     * @return string
     */
    public static function print($color, $message)
    {
        return $color . $message . self::COLOR_NC;
    }

    /**
     *
     *
     * @param string $message
     *
     * @return string
     */
    public static function success($message)
    {
        return self::print(self::COLOR_LIGHT_GREEN, $message);
    }

    /**
     * @param string $message
     *
     * @return string
     */
    public static function danger($message)
    {
        return self::print(self::COLOR_LIGHT_RED, $message);
    }

    /**
     * @param string $message
     *
     * @return string
     */
    public static function warning($message)
    {
        return self::print(self::COLOR_YELLOW, $message);
    }

    /**
     * @param string $message
     *
     * @return string
     */
    public static function info($message)
    {
        return self::print(self::COLOR_LIGHT_BLUE, $message);
    }
}