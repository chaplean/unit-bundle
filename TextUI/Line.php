<?php

namespace Chaplean\Bundle\UnitBundle\TextUI;

/**
 * Class Line.
 *
 * @package   Chaplean\Bundle\UnitBundle\TextUI
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2017 Chaplean (http://www.chaplean.coop)
 * @since     X.Y.Z
 */
class Line
{
    private $string;

    /**
     * String constructor.
     *
     * @param string $string
     */
    public function __construct($string)
    {
        $this->string = $string;
    }

    /**
     * @return integer
     */
    public function getLength()
    {
        $matches = null;
        preg_match_all('/\\e\[\d?;?\d{1,2}m/', $this->string, $matches);

        if (count($matches) > 0 && count($matches[0]) > 0) {
            $extraChars = array_sum(array_map(function ($s) {
                return strlen($s);
            }, $matches[0]));

            return strlen($this->string) - $extraChars;
        } else {
            return strlen($this->string);
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->string;
    }

}
