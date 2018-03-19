<?php

namespace Chaplean\Bundle\UnitBundle\TextUI;

use PHPUnit\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Filter;
use PHPUnit\Util\Test as TestUtil;
use SebastianBergmann\Environment\Console;

/**
 * Class Printer
 * @package Chaplean\Bundle\UnitBundle\TextUI
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2017 Chaplean (http://www.chaplean.coop)
 * @since     1.0.0
 */
class Printer
{
    /** Ansi escape */
    const ESC = "\x1b[";

    /** Ansi Normal */
    const NND = "\x1b[0m";

    /**
     * @var array
     */
    private $counter = [
        'error'   => 0,
        'fail'    => 0,
        'warning' => 0,
        'risky'   => 0,
        'skipped' => 0,
    ];
    /**
     * @var integer
     */
    private $height = 0;
    /**
     * @var array
     */
    private $messages = [];
    /**
     * @var callable
     */
    private $writer;
    /**
     * @var float
     */
    private $time;

    /**
     * Printer constructor.
     *
     * @param callable $writer
     */
    public function __construct(callable $writer)
    {
        $this->writer = $writer;
        $this->time = microtime(true);

//        $t = microtime(true);
//        $this->writeLines(Style::info(20) . ") Tests\App\Bundle\FrontBundle\Controller\SearchEngineControllerTest::testGetSearchForTrainingCourse with data set \"Admin\" ('user-5', 200)\n" . Style::danger("Argument 4 passed to App\Bundle\FrontBundle\Form\Type\TrainingSearchEngineType::__construct() must be an instance of Chaplean\Bundle\LocationBundle\Utility\CityUtility, instance of appTestDebugProjectContainer given, called in /var/www/symfony/var/cache/test/appTestDebugProjectContainer.php on line 1283") . "\n\n/var/www/symfony/src/App/Bundle/FrontBundle/Form/Type/TrainingSearchEngineType.php:64\n/var/www/symfony/var/cache/test/appTestDebugProjectContainer.php:1283\n/var/www/symfony/vendor/symfony/symfony/src/Symfony/Component/DependencyInjection/Container.php:297\n/var/www/symfony/vendor/symfony/symfony/src/Symfony/Component/Form/Extension/DependencyInjection/DependencyInjectionExtension.php:42\n/var/www/symfony/vendor/symfony/symfony/src/Symfony/Component/Form/FormRegistry.php:80\n/var/www/symfony/vendor/symfony/symfony/src/Symfony/Component/Form/FormFactory.php:64\n/var/www/symfony/vendor/symfony/symfony/src/Symfony/Component/Form/FormFactory.php:33\n/var/www/symfony/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Controller/Controller.php:282\n/var/www/symfony/src/App/Bundle/FrontBundle/Controller/SearchEngineController.php:49\n/var/www/symfony/vendor/symfony/symfony/src/Symfony/Component/HttpKernel/HttpKernel.php:137\n/var/www/symfony/vendor/symfony/symfony/src/Symfony/Component/HttpKernel/HttpKernel.php:57\n/var/www/symfony/vendor/symfony/symfony/src/Symfony/Component/HttpKernel/DependencyInjection/ContainerAwareHttpKernel.php:67\n/var/www/symfony/vendor/symfony/symfony/src/Symfony/Component/HttpKernel/Kernel.php:183\n/var/www/symfony/vendor/symfony/symfony/src/Symfony/Component/HttpKernel/Client.php:58\n/var/www/symfony/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Client.php:131\n/var/www/symfony/vendor/symfony/symfony/src/Symfony/Component/BrowserKit/Client.php:311\n/var/www/symfony/tests/App/Bundle/FrontBundle/Controller/SearchEngineControllerTest.php:53\n\n");
//        $this->resetCursor();
//        var_dump(microtime(true) - $t);die;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return void
     */
    protected function eraseLine()
    {
        $this->write("\033[2K");
    }

    /**
     * @return integer
     */
    public function getCurrentNumberColumns()
    {
        $console = new Console();

        return $console->getNumberOfColumns();
    }

    /**
     * @param string $type
     *
     * @return void
     */
    public function increase($type)
    {
        $this->counter[$type]++;
    }

    /**
     * @param TestCase|Test $test
     *
     * @return void
     */
    public function printBody(Test $test)
    {
        $this->writeLines(sprintf("%s\n  %s\n", get_class($test), $test->getName()));
    }

    /**
     * @param integer $numTestsRun
     * @param integer $numTests
     *
     * @return void
     */
    public function printHeader($numTestsRun, $numTests)
    {
        if ($this->height > 0) {
            $this->resetCursor();
        }

        $this->writeLines(sprintf(
            "Tests: %d/%d (%d%%) Errors: %s, Failures: \033[41m%s\033[0m, Skipped: %s, Warnings: %s, Risky: %s, Time elapsed: soon\n\n",
            $numTestsRun,
            $numTests,
            ($numTestsRun * 100) / $numTests,
            Style::danger($this->counter['error']),
            $this->counter['fail'],
            Style::print(Style::COLOR_LIGHT_CYAN, $this->counter['skipped']),
            Style::warning($this->counter['warning']),
            Style::warning($this->counter['risky'])
            //Timer::toString(microtime(true) - $this->time)
        ));
    }

    /**
     * @param Test                 $test
     * @param \Exception|Exception $e
     * @param string               $color
     *
     * @return void
     */
    public function printMessage(Test $test, Exception $e, $color = Style::COLOR_LIGHT_RED)
    {
        $index = count($this->messages) + 1;

        $message = Style::print($color, $e->getMessage());
        if ($e instanceof ExpectationFailedException && $e->getComparisonFailure()) {
            $message .= $e->getComparisonFailure()->getDiff();
        }

        $error = sprintf("\n%s) %s\n%s\n\n%s", Style::info($index), TestUtil::describe($test), $message, Filter::getFilteredStacktrace($e));
        $this->messages[] = $error;

        $this->writeLines($error);
    }

    /**
     * @return void
     */
    public function printMessages()
    {
        $this->writeLines(implode("\n", $this->messages));
    }

    /**
     * @return void
     */
    public function resetCursor()
    {
//        $this->write("\033[1A");
        while ($this->height-- >= 0) {
            $this->write("\033[1A");
            $this->eraseLine();
        }

        $this->height = 0;
    }

    /**
     * @param string $message
     *
     * @return void
     */
    public function write($message)
    {
        call_user_func($this->writer, $message);
    }

    /**
     * @param string $message
     *
     * @return void
     */
    public function writeLines($message)
    {
        $columns = $this->getCurrentNumberColumns();
        $matches = null;
        preg_match_all('/(.+)\\n/', $message, $matches);
        $height = substr_count($message, "\n") - 1;
        $height += array_sum(array_map(function ($str) use ($columns) {
            $matches = null;
            preg_match_all('/\\e\[\d?;?\d{1,2}m/', $str, $matches);

            if (count($matches) > 0 && count($matches[0]) > 0) {
                $extraChars = array_sum(array_map(function ($s) {
                    return strlen($s);
                }, $matches[0]));

                $length = strlen($str) - $extraChars;
            } else {
                $length = strlen($str);
            }

            return floor($length / $columns);
        }, $matches[1]));

        $this->height += $height;
        $this->write($message);

//        $messages = explode("\n", $message);
//        $nbMessage = count($messages);
//        $height = $nbMessage - 1;
//
//        foreach ($messages as $key => $string) {
//            $line = new Line($string);
//            $height += floor($line->getLength() / $columns);
//
//            $this->eraseLine();
//            $this->write($string . (($key !== $nbMessage - 1) ? "\n" : ''));
//        }
//
//        $this->height += $height;
        sleep(1);
    }
}
