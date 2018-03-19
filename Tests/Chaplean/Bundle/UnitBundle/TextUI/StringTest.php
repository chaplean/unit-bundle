<?php

namespace Tests\Chaplean\Bundle\UnitBundle\TextUI;

use Chaplean\Bundle\UnitBundle\TextUI\Line;
use Chaplean\Bundle\UnitBundle\TextUI\Style;
use PHPUnit\Framework\TestCase;

/**
 * Class LineTest.
 *
 * @package   Tests\Chaplean\Bundle\UnitBundle\TextUI
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2017 Chaplean (http://www.chaplean.coop)
 * @since     X.Y.Z
 */
class LineTest extends TestCase
{
    /**
     * @covers \Chaplean\Bundle\UnitBundle\TextUI\Line::__construct
     * @covers \Chaplean\Bundle\UnitBundle\TextUI\Line::__toLine
     *
     * @return void
     */
    public function testToLine()
    {
        $this->assertEquals('a', (string) new Line('a'));
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\TextUI\Line::getLength()
     *
     * @return void
     */
    public function testGetLength()
    {
        $s = new Line('a');

        $this->assertEquals(1, $s->getLength());

        $s = new Line(Style::success('a'));

        $this->assertEquals(1, $s->getLength());

        $s = new Line(sprintf("Header: %s, Failures: \033[41m%s\033[0m, Errors: %s", 1, 8, Style::danger(5)));

        $this->assertEquals(33, $s->getLength());
    }
}
