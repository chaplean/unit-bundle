<?php

namespace Chaplean\Bundle\UnitBundle\Listener;

use Chaplean\Bundle\UnitBundle\Model\TimerTest;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;

/**
 * Class TestListener.
 *
 * @package   Chaplean\Bundle\UnitBundle\Listener
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2017 Chaplean (http://www.chaplean.coop)
 * @since     X.Y.Z
 */
class TestListener implements \PHPUnit\Framework\TestListener
{
    private $testSuite = 0;
    private $timeTests = [];

    /**
     * @var boolean
     */
    public static $run = false;

    /**
     * @param boolean $run
     */
    public static function setRun($run)
    {
        var_dump($run);
        self::$run = $run;
    }



    /**
     * An error occurred.
     *
     * @param Test       $test
     * @param \Exception $e
     * @param float      $time
     */
    public function addError(Test $test, \Exception $e, $time)
    {
    }

    /**
     * A warning occurred.
     *
     * @param Test    $test
     * @param Warning $e
     * @param float   $time
     */
    public function addWarning(Test $test, Warning $e, $time)
    {
    }

    /**
     * A failure occurred.
     *
     * @param Test                 $test
     * @param AssertionFailedError $e
     * @param float                $time
     */
    public function addFailure(Test $test, AssertionFailedError $e, $time)
    {
    }

    /**
     * Incomplete test.
     *
     * @param Test       $test
     * @param \Exception $e
     * @param float      $time
     */
    public function addIncompleteTest(Test $test, \Exception $e, $time)
    {
    }

    /**
     * Risky test.
     *
     * @param Test       $test
     * @param \Exception $e
     * @param float      $time
     */
    public function addRiskyTest(Test $test, \Exception $e, $time)
    {
    }

    /**
     * Skipped test.
     *
     * @param Test       $test
     * @param \Exception $e
     * @param float      $time
     */
    public function addSkippedTest(Test $test, \Exception $e, $time)
    {
    }

    /**
     * A test suite started.
     *
     * @param TestSuite $suite
     */
    public function startTestSuite(TestSuite $suite)
    {
        $this->testSuite++;
    }

    /**
     * A test suite ended.
     *
     * @param TestSuite $suite
     */
    public function endTestSuite(TestSuite $suite)
    {
        $this->testSuite--;

//        if ($this->testSuite == 0) {
//            echo "\n";
//            foreach ($this->timeTests as $class => $timeTest) {
//                echo "\n\e[4m" . $class . "\e[0m:";
//                foreach ($timeTest as $item) {
//                    echo "\n" . $item;
//                }
//            }
//        }
    }

    /**
     * A test started.
     *
     * @param Test $test
     */
    public function startTest(Test $test)
    {
    }

    /**
     * A test ended.
     *
     * @param Test  $test
     * @param float $time
     */
    public function endTest(Test $test, $time)
    {
        $this->timeTests[get_class($test)][] = new TimerTest($test->getName(), $time);
    }
}
