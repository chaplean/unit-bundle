<?php

namespace Tests\Chaplean\Bundle\UnitBundle\TextUI;

use Chaplean\Bundle\UnitBundle\TextUI\Printer;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class PrinterTest.
 *
 * @package   Tests\Chaplean\Bundle\UnitBundle\TextUI
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2017 Chaplean (http://www.chaplean.coop)
 * @since     7.0.1
 */
class PrinterTest extends MockeryTestCase
{
    /**
     * @var OutputTest
     */
    private $output;

    /**
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->output = new OutputTest();
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\TextUI\Printer::__construct
     * @covers \Chaplean\Bundle\UnitBundle\TextUI\Printer::write()
     *
     * @return void
     */
    public function testWrite()
    {
        $printer = new Printer([$this->output, 'write']);

        $printer->write('foo');

        $this->assertEquals('foo', $this->output);
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\TextUI\Printer::writeLines()
     *
     * @return void
     */
    public function testWriteLines()
    {
        /** @var Printer|\Mockery\MockInterface $printer */
        $printer = \Mockery::mock(Printer::class, [[$this->output, 'write']])
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        $printer->shouldReceive('eraseLine');

        $printer->writeLines("Lines\nBar");

        $this->assertEquals("Lines\nBar", (string) $this->output);
    }
}

class OutputTest {
    /** @var string $stdout */
    public $stdout = '';

    /**
     * @param string $string
     *
     * @return void
     */
    public function write($string)
    {
        $this->stdout .= $string;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->stdout;
    }
}
