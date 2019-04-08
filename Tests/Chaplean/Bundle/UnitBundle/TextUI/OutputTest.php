<?php

namespace Tests\Chaplean\Bundle\UnitBundle\TextUI;

use Chaplean\Bundle\UnitBundle\TextUI\Output;
use PHPUnit\Framework\TestCase;

/**
 * Class OutputTest.
 *
 * @package   Tests\Chaplean\Bundle\UnitBundle\TextUI
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2017 Chaplean (https://www.chaplean.coop)
 * @since     7.0.0
 */
class OutputTest extends TestCase
{
    /**
     * @covers \Chaplean\Bundle\UnitBundle\TextUI\Output::success()
     *
     * @return void
     */
    public function testSuccess()
    {
        $this->assertEquals("\e[1;32mgood\e[0m", Output::success('good'));
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\TextUI\Output::danger()
     *
     * @return void
     */
    public function testDanger()
    {
        $this->assertEquals("\e[1;31mbad\e[0m", Output::danger('bad'));
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\TextUI\Output::warning()
     *
     * @return void
     */
    public function testWarning()
    {
        $this->assertEquals("\e[1;33mwarn\e[0m", Output::warning('warn'));
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\TextUI\Output::info()
     *
     * @return void
     */
    public function testInfo()
    {
        $this->assertEquals("\e[1;34minfo\e[0m", Output::info('info'));
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\TextUI\Output::print()
     *
     * @return void
     */
    public function testPrint()
    {
        $this->assertEquals("\e[0;36mcolorme\e[0m", Output::print(Output::COLOR_CYAN, 'colorme'));
    }
}
