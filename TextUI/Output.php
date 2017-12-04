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
    const COLOR_NC = "\e[0m"; # No Color
    const COLOR_WHITE = "\e[1;37m";
    const COLOR_BLACK = "\e[0;30m";
    const COLOR_BLUE = "\e[0;34m";
    const COLOR_LIGHT_BLUE = "\e[1;34m";
    const COLOR_GREEN = "\e[0;32m";
    const COLOR_LIGHT_GREEN = "\e[1;32m";
    const COLOR_CYAN = "\e[0;36m";
    const COLOR_LIGHT_CYAN = "\e[1;36m";
    const COLOR_RED = "\e[0;31m";
    const COLOR_LIGHT_RED = "\e[1;31m";
    const COLOR_PURPLE = "\e[0;35m";
    const COLOR_LIGHT_PURPLE = "\e[1;35m";
    const COLOR_BROWN = "\e[0;33m";
    const COLOR_YELLOW = "\e[1;33m";
    const COLOR_GRAY = "\e[0;30m";
    const COLOR_LIGHT_GRAY = "\e[0;37m";

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