<?php

namespace Chaplean\Bundle\UnitBundle\TextUI;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\Warning;
use SebastianBergmann\Environment\Console;

/**
 * Class ResultPrinter.
 *
 * @package   Chaplean\Bundle\UnitBundle\TextUI
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2017 Chaplean (http://www.chaplean.coop)
 * @since     7.0.0
 */
class ResultPrinter extends \PHPUnit\TextUI\ResultPrinter
{
    private $printer;

    /**
     * ResultPrinter constructor.
     *
     * @param null    $out
     * @param boolean $verbose
     * @param string  $colors
     * @param boolean $debug
     * @param integer $numberOfColumns
     * @param boolean $reverse
     */
    public function __construct($out = null, $verbose = false, $colors = \PHPUnit\TextUI\ResultPrinter::COLOR_DEFAULT, $debug = false, $numberOfColumns = 80, $reverse = false)
    {
        parent::__construct($out, $verbose, $colors, $debug, $numberOfColumns, $reverse);

        $this->printer = new Printer([$this, 'write']);
    }

    /**
     * @param Test $test
     *
     * @return void
     */
    public function startTest(Test $test)
    {
        if ($this->debug) {
            parent::startTest($test);
            return;
        }

        $this->numTestsRun++;

        $this->printer->printHeader($this->numTestsRun, $this->numTests);
        $this->printer->printBody($test);
        $this->printer->printMessages();

        parent::startTest($test);
    }

    /**
     * @param string $progress
     *
     * @return void
     */
    protected function writeProgress($progress)
    {
        if ($this->debug) {
            parent::writeProgress($progress);
        }

        return;
    }

    /**
     * @param \PHPUnit\Framework\Test $test
     * @param \Exception              $e
     * @param float                   $time
     *
     * @return void
     */
    public function addError(Test $test, \Exception $e, $time)
    {
        if ($this->debug) {
            parent::addError($test, $e, $time);
            return;
        }

        $this->printer->increase('error');
        $this->printer->printMessage($test, $e);

        $this->lastTestFailed = true;
    }

    /**
     * @param Test                 $test
     * @param AssertionFailedError $e
     * @param float                $time
     *
     * @return void
     */
    public function addFailure(Test $test, AssertionFailedError $e, $time)
    {
        if ($this->debug) {
            parent::addFailure($test, $e, $time);
            return;
        }

        $this->printer->increase('fail');
        $this->printer->printMessage($test, $e);

        $this->lastTestFailed = true;
    }

    /**
     * @param \PHPUnit\Framework\Test $test
     * @param \Exception              $e
     * @param float                   $time
     *
     * @return void
     */
    public function addRiskyTest(Test $test, \Exception $e, $time)
    {
        if ($this->debug) {
            parent::addRiskyTest($test, $e, $time);
            return;
        }

        $this->printer->increase('risky');
        $this->printer->printMessage($test, $e, Style::COLOR_YELLOW);

        $this->lastTestFailed = true;
    }

    /**
     * @param \PHPUnit\Framework\Test $test
     * @param \Exception              $e
     * @param float                   $time
     *
     * @return void
     */
    public function addSkippedTest(Test $test, \Exception $e, $time)
    {
        if ($this->debug) {
            parent::addSkippedTest($test, $e, $time);
            return;
        }

        $this->printer->increase('skipped');
    }

    /**
     * @param \PHPUnit\Framework\Test    $test
     * @param \PHPUnit\Framework\Warning $e
     * @param float                      $time
     *
     * @return void
     */
    public function addWarning(Test $test, Warning $e, $time)
    {
        if ($this->debug) {
            parent::addWarning($test, $e, $time);
            return;
        }

        $this->printer->increase('warning');
        $this->printer->printMessage($test, $e, Style::COLOR_YELLOW);

        $this->lastTestFailed = true;
    }

    /**
     * @param \PHPUnit\Framework\TestResult $result
     *
     * @return void
     */
    protected function printErrors(TestResult $result)
    {
        if ($this->debug) {
            parent::printErrors($result);
        }
    }

    /**
     * @param \PHPUnit\Framework\TestResult $result
     *
     * @return void
     */
    protected function printFailures(TestResult $result)
    {
        if ($this->debug) {
            parent::printFailures($result);
        }
    }

    /**
     * @param \PHPUnit\Framework\TestResult $result
     *
     * @return void
     */
    protected function printRisky(TestResult $result)
    {
        if ($this->debug) {
            parent::printRisky($result);
        }
    }

    /**
     * @param \PHPUnit\Framework\TestResult $result
     *
     * @return void
     */
    protected function printWarnings(TestResult $result)
    {
        if ($this->debug) {
            parent::printWarnings($result);
        }
    }
}
