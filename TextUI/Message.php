<?php

namespace Chaplean\Bundle\UnitBundle\TextUI;

/**
 * Class Message
 * @package Chaplean\Bundle\UnitBundle\TextUI
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2017 Chaplean (http://www.chaplean.coop)
 * @since     1.0.0
 */
class Message
{
    /** @var string */
    private $describe;
    /** @var string */
    private $message;
    /** @var string */
    private $trace;

    /**
     * Message constructor.
     *
     * @param string $describe
     * @param string $message
     * @param string $trace
     */
    public function __construct($describe, $message, $trace)
    {
        $this->describe = $describe;
        $this->message = $message;
        $this->trace = $trace;
    }

    /**
     * @param integer $numberColumns
     *
     * @return integer
     */
    public function getHeight($numberColumns)
    {
        $height = 3;

        $height += ceil(strlen($this->describe) / $numberColumns);
        $height += ceil(strlen($this->message) / $numberColumns);
        $height += ceil(strlen($this->trace) / $numberColumns);

        return $height;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf("%s\n%s\n\n%s", $this->describe, $this->message, $this->trace);
    }
}