<?php

namespace Chaplean\Bundle\UnitBundle\Features\Context;

use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Element\DocumentElement;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ExpectationException;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Chaplean\Bundle\UnitBundle\Utility\ContainerUtility;
use Chaplean\Bundle\UnitBundle\Utility\FixtureUtility;
use Chaplean\Bundle\UnitBundle\Utility\NamespaceUtility;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class FeatureContext.
 *
 * @package   Chaplean\Bundle\UnitBundle\Features\Context
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     1.1.0
 */
class ChapleanContext extends MinkContext implements KernelAwareContext
{
    use KernelDictionary;

    /**
     * @var array
     */
    protected static $dataFixtures = array();

    /**
     * @var boolean
     */
    protected static $databaseLoaded = false;

    /**
     * Checks that passed Element has passed Class.
     *
     * @Then /^the element "(?P<element>(?:[^"]|\\")*)" has class "(?P<class>(?:[^"]|\\")*)"$/
     *
     * @param string $element
     * @param string $class
     *
     * @return void
     */
    public function assertElementHasClass($element, $class)
    {
        $this->assertElementOnPage($element . '.' . $class);
    }

    /**
     * Checks that a CSS element is NOT visible in the page
     *
     * @Then /^(?:|I )should not see a visible "(?P<element>[^"]*)" element$/
     *
     * @param string $element Searched element
     *
     * @return void
     * @throws \Exception
     */
    public function assertNotVisibleElement($element)
    {
        $this->assertVisibleElements(0, $element);
    }

    /**
     * Checks, that current page PATH is not equal to specified.
     *
     * @Then /^(?:|I )should not be on "(?P<page>[^"]+)"$/
     *
     * @param string $page
     *
     * @return void
     */
    public function assertPageAddressIsNot($page)
    {
        $this->assertSession()->addressNotEquals($this->locatePath($page));
    }

    /**
     * Checks that (?P<num>\d+) CSS elements are visible in the page
     *
     * @Then /^(?:|I )should see (?P<num>\d+) visibles? "(?P<element>[^"]*)" elements?$/
     *
     * @param integer $num     Number of search element
     * @param string  $element Searched element
     *
     * @return void
     * @throws \Exception
     */
    public function assertVisibleElements($num, $element)
    {
        $session = $this->getSession();
        $nodes = $session->getPage()->findAll('css', $element);

        $nbVisibles = 0;

        try {
            /** @var NodeElement $node */
            foreach ($nodes as $node) {
                if ($node->isVisible()) {
                    $nbVisibles++;
                }
            }
        } catch (\Exception $e) {
            // Empty 'cause no need to do anything
        }

        if ($nbVisibles != $num) {
            throw new \Exception($num . ' element(s) ' . $element . ' not visible.');
        }
    }

    /**
     * Checks that 1 CSS element is visible in the page
     *
     * @Then /^(?:|I )should see a visible "(?P<element>[^"]*)" element$/
     *
     * @param string $element Searched element
     *
     * @return void
     * @throws \Exception
     */
    public function assertVisibleElement($element)
    {
        $this->assertVisibleElements(1, $element);
    }

    /**
     * @BeforeScenario
     *
     * @return void
     */
    public function beforeScenario()
    {
//        $session = $this->getSession();
//        $driver = $session->getDriver();
//
//        $driver->resizeWindow(1920, 1080);
//        $hasBind = $session->evaluateScript('(typeof Function.prototype.bind == \'function\')');
//        if (!$hasBind) {
//            $bind = file_get_contents(__DIR__ . '/../../Resources/public/js/polyfill-bind.js');
//
//        }
    }

    /**
     * @BeforeScenario
     * @return void
     */
    public function cleanMailDir()
    {
        $this->getContainer()->get('chaplean_unit.swiftmailer_cache')->cleanMailDir();
    }

    /**
     * Fills in form field with specified element.
     *
     * @When /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" element with "(?P<value>(?:[^"]|\\")*)"$/
     *
     * @param string $field
     * @param string $value
     *
     * @return void
     */
    public function fillFieldElement($field, $value)
    {
        $field = $this->fixStepArgument($field);
        $value = $this->fixStepArgument($value);
        $page = $this->getSession()->getPage();

        $node = $page->find('css', $field);
        $node->setValue($value);
    }

    /**
     * Get the container
     *
     * @return Container
     */
    public static function getStaticContainer()
    {
        return ContainerUtility::getContainer('behat');
    }

    /**
     * Add datfixture
     *
     * @Given /^I add datafixture "(?P<datafixture>(?:[^"]|\\")*)"$/
     *
     * @param string $datafixture
     *
     * @return void
     */
    public function iAddDatafixture($datafixture)
    {
        self::$dataFixtures[] = $datafixture;
    }

    /**
     *  Click on element with css
     *
     * @When /^(?:|I )click on "(?P<element>(?:[^"]|\\")*)"$/
     *
     * @param string $element
     *
     * @return void
     * @throws \Exception
     */
    public function iClickOn($element)
    {
        $page = $this->getSession()->getPage();
        $element = $page->find('css', $element);

        $this->iClick($element, $page);
    }

    /**
     *  Click on link
     *
     * @When /^(?:|I )click on link "(?P<link>(?:[^"]|\\")*)"$/
     *
     * @param string $link
     *
     * @return void
     * @throws \Exception
     */
    public function iClickOnLink($link)
    {
        $page = $this->getSession()->getPage();
        $element = $page->findLink($link);

        $this->iClick($element, $page);
    }

    /**
     * @param null|NodeElement $element
     * @param DocumentElement  $page
     *
     * @return void
     * @throws \Exception
     */
    private function iClick($element, $page)
    {
        if (!empty($element)) {
            $element->click();
        } else {
            throw new \Exception(error_get_last() . ' ' . $page->getContent());
        }
    }

    /**
     *  Focus an iframe
     *
     * @When /^(?:|I )focus the iframe "(?P<iframe>(?:[^"]|\\")*)"$/
     *
     * @param string $iframe
     *
     * @return void
     */
    public function iFocusIframe($iframe)
    {
        $session = $this->getSession();
        $session->switchToIFrame($iframe);
    }

    /**
     * Wait some milliseconds
     *
     * @When /^(?:|I )wait (?P<time>(?:[^"]|\\")*) millisec$/
     *
     * @param integer $time
     *
     * @return void
     */
    public function iWait($time)
    {
        $this->getSession()->wait($time);
    }

    /**
     * @When /^I wait Ajax$/
     *
     * @return void
     * @throws \Behat\Mink\Exception\ExpectationException
     * @throws \Exception
     */
    public function iWaitAjax()
    {
        $waitTime = 5000;
        try {
            //Wait for Angular
            $angularIsNotUndefined = $this->getSession()->evaluateScript("return (typeof angular != 'undefined')");
            if ($angularIsNotUndefined) {
                //If you run the below code on a page ending in #, the page reloads.
                if (substr($this->getSession()->getCurrentUrl(), -1) !== '#') {
                    $angular = 'angular.getTestability(document.body).whenStable(function() {window.__testable = true;})';
                    $this->getSession()->evaluateScript($angular);
                    $this->getSession()->wait($waitTime, 'window.__testable == true');
                }

                /*
                 * Angular JS AJAX can't be detected overall like in jQuery,
                 * but we can check if any of the html elements are marked as showing up when ajax is running,
                 * then wait for them to disappear.
                 */
                $ajaxRunningXPath = "xpath://*[@ng-if='ajax_running']";
                $this->waitForElementToDisappear($waitTime, $ajaxRunningXPath);
            }

            //Wait for jQuery
            if ($this->getSession()->evaluateScript("return (typeof jQuery != 'undefined')")) {
                $this->getSession()->wait($waitTime, '(0 === jQuery.active && 0 === jQuery(\':animated\').length)');
            }
        } catch (Exception $e) {
            throw new \Exception($e->getMessage()); //Debug here.
        }
    }

    /**
     * @param integer $time
     * @param string  $fullLocator
     *
     * @return void
     * @throws \Behat\Mink\Exception\ExpectationException
     */
    public function waitForElementToDisappear($time, $fullLocator)
    {
        $page = $this->getSession()->getPage();
        list($selector, $locator) = $this->expandLocatorDefintion($fullLocator);
        $start = 1000 * microtime(true);
        $end = $start + $time;
        $element = $page->find($selector, $locator);
        while (1000 * microtime(true) < $end && $element !== null) {
            sleep(0.1);
            $element = $page->find($selector, $locator);
        }
        if ($element !== null) {
            $message = sprintf('Element %s has not disappeared after %sms timeout.', $locator, $time);
            throw new ExpectationException($message, $this->getSession());
        }
    }

    /**
     * @param string $locator
     * @return array
     */
    public function expandLocatorDefintion($locator)
    {
        if (substr($locator, 0, 6) == 'xpath:') {
            return array('xpath', substr($locator, 6));
        } elseif (substr($locator, 0, 4) == 'css:') {
            return array('css', substr($locator, 4));
        } else {
            return array('named', $locator);
        }
    }

    /**
     * Waiting ajax Angular call
     *
     * @When /^(?:|I )wait for Angular$/
     *
     * @return void
     */
    public function iWaitForAngular()
    {
        // Wait for angular to load
        $this->getSession()->wait(5000, "typeof angular != 'undefined'");
        // Wait for angular to be testable
        $this->getSession()->evaluateScript(
            'angular.getTestability(document.body).whenStable(function() {
                window.__testable = true;
            })'
        );
        $this->getSession()->wait(5000, 'window.__testable == true');
    }

    /**
     * Load fixture with datafixtures added, otherwise empty database
     *
     * @Given /^I load database$/
     *
     * @return void
     */
    public function iLoadDatabase()
    {
        if (!self::$databaseLoaded) {
            self::$databaseLoaded = true;
            FixtureUtility::loadFixtures(self::$dataFixtures, 'behat');
        }
    }

    /**
     * Load default datafixture
     *
     * @Given /^I load default datafixture with "(?P<namespace>(?:[^"]|\\")*)"$/
     * @Given /^I load default datafixture$/
     *
     * @param string $namespace
     *
     * @return void
     */
    public function iLoadDefaultFixtures($namespace = null)
    {
        self::$dataFixtures = FixtureUtility::loadDefaultFixtures($namespace);
    }

    /**
     * Load default datafixture
     *
     * @Given /^I load context datafixture with "(?P<context>(?:[^"]|\\")*)"$/
     *
     * @param string $context
     *
     * @return void
     */
    public function iLoadByContextFixtures($context)
    {
        self::$dataFixtures = NamespaceUtility::getClassNamesByContext(FixtureUtility::$namespace, $context);

        $this->iLoadDatabase();
    }

    /**
     * @Then /^(?:|the )"(?P<type>[^"]+)" mail should be sent to "(?P<email>[^"]+)"$/
     *
     * @param string $type
     * @param string $email
     *
     * @return void
     */
    public function theMailShouldBeSentTo($type, $email)
    {
        $messages = $this->readMessages();

        if (gettype($messages) == 'object') {
            $messages = array($messages);
        }

        foreach ($messages as $message) {
            /** @noinspection PhpUndefinedMethodInspection */
            $recipients = array_keys($message->getTo());

            if (in_array($email, $recipients)) {
                return;
            }
        }

        throw new Exception(sprintf('The "%s" was not sent', $type));
    }

    /**
     * @When /^(?:|I )click on the first link in the last mail sent$/
     *
     * @return void
     */
    public function iClickLinkInMail()
    {
        $messages = $this->readMessages();

        if (gettype($messages) == 'object') {
            $messages = array($messages);
        }

        foreach ($messages as $message) {
            /** @noinspection PhpUndefinedMethodInspection */
            $body = array_keys($message->getBody());

            $matches = array();
            preg_match('#<a[^>]*href="([^"]*)"[^>]*>.*</a>#', $body, $matches);

            if (!isset($matches[1])) {
                throw new Exception('No link found in the mail');
            }

            $this->visitPath($matches[1]);
        }

        throw new Exception('No link found in the mail');
    }

    /**
     * @BeforeFeature
     *
     * @return void
     */
    public static function resetLoadedDatabase()
    {
        self::$databaseLoaded = false;

        $file = new \ReflectionClass(get_called_class());
        $name = $file->name;
        FixtureUtility::$namespace = substr($name, 0, strpos($name, 'Features\Context'));
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function readMessages()
    {
        return $this->getContainer()->get('chaplean_unit.swiftmailer_cache')->readMessages();
    }

    /**
     * @When /^take a screenshot$/
     *
     * @return void
     */
    public function takeAScreenshot()
    {
        if ($this->getSession()->getDriver() instanceof Selenium2Driver) {
            $screenshot = $this->getSession()->getDriver()->getScreenshot();
            file_put_contents('/tmp/screenshot.png', $screenshot);
        }
    }

    /**
     * @AfterStep
     *
     * @param AfterStepScope $scope
     *
     * @return void
     * @throws \Exception
     */
    public function waitAjax(AfterStepScope $scope)
    {
        if (preg_match('/I? am on "(.?[^"]+)"/', $scope->getStep()->getText())) {
            $this->iWaitAjax();
        }
    }
}
