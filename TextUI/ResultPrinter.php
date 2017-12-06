<?php

namespace Chaplean\Bundle\UnitBundle\TextUI;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\Warning;
use PHPUnit\Util\Filter;
use PHPUnit\Util\Test as TestUtil;
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
    /** @var string */
    protected $className;
    /** @var array */
    protected $counter = [
        'error'   => 0,
        'fail'    => 0,
        'warning' => 0,
        'risky'   => 0,
    ];
    /** @var integer  */
    protected $height = 4;
    /** @var integer */
    protected $numberColumns;
    /** @var string  */
    protected $messages = '';
    /** @var integer  */
    protected $skipped = 0;

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

        $console = new Console();
        $this->numberColumns = $console->getNumberOfColumns();
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

        if ($this->numTestsRun > 1) {
            $this->write(Output::ESC . $this->height . 'A');
        }

        $this->write(sprintf('Tests: %d/%d (%d%%)', $this->numTestsRun, $this->numTests, ($this->numTestsRun * 100) / $this->numTests));
        $this->write(' Errors: ' . Output::danger($this->counter['error']));
        $this->write(", Failures: \033[41m" . $this->counter['fail'] . "\033[0m");
        $this->write(', Skipped: ' . Output::print(Output::COLOR_LIGHT_CYAN, $this->skipped));
        $this->write(', Warning: ' . Output::warning($this->counter['warning']));
        $this->write(', Risky: ' . Output::warning($this->counter['risky']));

        $testName = $test instanceof TestCase ? $test->getName() : TestUtil::describe($test);
        $this->write(sprintf(
            "\n\n%s\n%s\n",
            str_pad(get_class($test), $this->numberColumns, ' '),
            str_pad('  ' . $testName, $this->numberColumns, ' ')
        ));

        $this->write($this->messages);

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

        $this->counter['error']++;

        $this->write($this->buildMessage($test, $e));

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

        $this->counter['fail']++;

        $this->write($this->buildMessage($test, $e));

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

        $this->counter['risky']++;

        $this->write($this->buildMessage($test, $e, Output::COLOR_YELLOW));

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

        $this->skipped++;
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

        $this->counter['warning']++;

        $this->buildMessage($test, $e, Output::COLOR_YELLOW);

        $this->lastTestFailed = true;
    }

    /**
     * @param Test                 $test
     * @param \Exception|Exception $e
     * @param string               $color
     *
     * @return string
     */
    private function buildMessage(Test $test, Exception $e, $color = Output::COLOR_LIGHT_RED)
    {
        $index = Output::info(array_sum($this->counter)) . ') ';
        $testName = TestUtil::describe($test);
        $trace = Filter::getFilteredStacktrace($e);
        $message = $e->getMessage();

        $message = sprintf("\n%s%s\n%s\n\n%s", $index, $testName, Output::print($color, $message), $trace);

        $traces = explode("\n", $trace);

        $this->height += (3 + (ceil(strlen($message)/$this->numberColumns)) + (count($traces) - 1));

        $this->messages .= $message;

        return $message;
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
