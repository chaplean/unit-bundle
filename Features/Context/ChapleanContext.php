<?php

namespace Chaplean\Bundle\UnitBundle\Features\Context;

use Behat\Mink\Element\NodeElement;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Chaplean\Bundle\UnitBundle\Utility\FixtureUtility;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Finder\Finder;

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
    private $dataFixtures = array();

    /**
     * @var boolean
     */
    private $isLoaded = false;

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
     * @return string
     */
    protected function getSpoolDir()
    {
        return $this->getContainer()->getParameter('swiftmailer.spool.default.file.path');
    }

    /**
     * Get the container
     *
     * @return Container
     */
    public static function getStaticContainer()
    {
        return FixtureUtility::getContainer('behat');
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
        $this->dataFixtures[] = $datafixture;
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

        if (!empty($element)) {
            $element->click();
        } else {
            throw new \Exception(error_get_last() . ' ' . $page->getContent());
        }
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
        FixtureUtility::loadFixtures($this->dataFixtures, 'behat');
    }

    /**
     * Load default datafixture
     *
     * @Given /^I load all default datafixture "(?P<namespace>(?:[^"]|\\")*)"$/
     *
     * @param string $namespace
     *
     * @return void
     */
    public function iLoadAllDefaultDatafixture($namespace)
    {
        $container = $this->getContainer();

        /** @var EntityManager $em */
        $em = $container->get('doctrine')->getManager();

        $listTables = $em->getMetadataFactory()->getAllMetadata();
        $datafixtures = array();

        /** @var ClassMetadata $table */
        foreach ($listTables as $table) {
            $class = new \ReflectionClass($table->getName());
            $fixtureClass = $namespace . '\\DataFixtures\\Liip\\Load' . $class->getShortName() . 'Data';
            if (class_exists($fixtureClass)) {
                $datafixtures[] = $fixtureClass;
            }
        }

        FixtureUtility::loadFixtures($datafixtures, 'behat');
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
        $spoolDir = $this->getSpoolDir();

        $finder = new Finder();

        // find every files inside the spool dir except hidden files
        $finder->in($spoolDir)->ignoreDotFiles(true)->files();

        foreach ($finder as $file) {
            $message = unserialize(file_get_contents($file));

            // check the recipients
            $recipients = array_keys($message->getTo());
            if (in_array($email, $recipients)) {
                return;
            }
        }

        throw new Exception(sprintf('The "%s" was not sent', $type));
    }
}
