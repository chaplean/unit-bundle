<?php

namespace Tests\Chaplean\Bundle\UnitBundle\TextUI;

use Chaplean\Bundle\UnitBundle\TextUI\Style;
use PHPUnit\Framework\TestCase;

/**
 * Class StyleTest.
 *
 * @package   Tests\Chaplean\Bundle\UnitBundle\TextUI
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2017 Chaplean (http://www.chaplean.coop)
 * @since     7.0.0
 */
class StyleTest extends TestCase
{
    /**
     * @covers \Chaplean\Bundle\UnitBundle\TextUI\Style::success()
     *
     * @return void
     */
    public function testSuccess()
    {
        $this->assertEquals("\033[1;32mgood\033[0m", Style::success('good'));
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\TextUI\Style::danger()
     *
     * @return void
     */
    public function testDanger()
    {
        $this->assertEquals("\033[1;31mbad\033[0m", Style::danger('bad'));
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\TextUI\Style::warning()
     *
     * @return void
     */
    public function testWarning()
    {
        $this->assertEquals("\033[1;33mwarn\033[0m", Style::warning('warn'));
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\TextUI\Style::info()
     *
     * @return void
     */
    public function testInfo()
    {
        $this->assertEquals("\033[1;34minfo\033[0m", Style::info('info'));
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\TextUI\Style::print()
     *
     * @return void
     */
    public function testPrint()
    {
        $this->assertEquals("\033[0;36mcolorme\033[0m", Style::print(Style::COLOR_CYAN, 'colorme'));
    }


    public function testFail1()
    {
        $this->assertGreaterThan(5, 1);
    }


    public function testFail2()
    {
        $this->assertEquals('6565', '9788');
    }

    public function testError()
    {
        Style::methodNotExist();
    }

    public function testMockFail()
    {
        $mock = \Mockery::mock(Style::class);
        $mock->shouldReceive('foo')->once();

        \Mockery::close();
    }

    public function testWarnin()
    {
        self::markAsRisky();
    }
}
