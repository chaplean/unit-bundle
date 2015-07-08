<?php

namespace Chaplean\Bundle\UnitBundle\Features\Context;

use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Chaplean\Bundle\UnitBundle\Utility\FixtureUtility;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\ProxyReferenceRepository;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\DBAL\Driver\PDOSqlite\Driver as SqliteDriver;

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
    private static $cachedMetadatas = array();

    /**
     * @var array
     */
    private $dataFixtures = array();

    /**
     * Fills in form field with specified element.
     *
     * @When /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" element with "(?P<value>(?:[^"]|\\")*)"$/
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
     *  Click on element with css
     *
     * @When /^(?:|I )click on "(?P<element>(?:[^"]|\\")*)"$/
     *
     * @param string $element
     *
     * @return void
     */
    public function iClickOn($element)
    {
        $page = $this->getSession()->getPage();
        $element = $page->find('css', $element);

        if (!empty($element)) {
            $element->click();
        } else {
            var_dump($page->getContent(), error_get_last());
            exit;
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
     */
    public function iClickOnLink($link)
    {
        $page = $this->getSession()->getPage();
        $element = $page->findLink($link);

        if (!empty($element)) {
            $element->click();
        } else {
            var_dump($page->getContent(), error_get_last());
            exit;
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
        $session =  $this->getSession();
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
     * Load fixture with datafixtures added, otherwise empty database
     *
     * @Given /^I load database$/
     *
     * @return void
     */
    public function iLoadDatabase()
    {
        FixtureUtility::loadFixtures($this->dataFixtures);
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

        FixtureUtility::loadFixtures($datafixtures);
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
}