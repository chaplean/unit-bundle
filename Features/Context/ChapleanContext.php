<?php

namespace Chaplean\Bundle\UnitBundle\Features\Context;

use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\ProxyReferenceRepository;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
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
     * Click on element with css
     *
     * @When /^(?:|I )click on "(?P<element>(?:[^"]|\\")*)"$/
     */
    public function iClickOn($element)
    {
        $page = $this->getSession()->getPage();

        $page->find('css', $element)->click();
    }

    /**
     *
     * Wait some milliseconds
     *
     * @When /^(?:|I )wait (?P<time>(?:[^"]|\\")*) millisec$/
     */
    public function iWait($time)
    {
        $this->getSession()->wait($time);
    }

    /**
     * Checks, that current page PATH is not equal to specified.
     *
     * @Then /^(?:|I )should not be on "(?P<page>[^"]+)"$/
     */
    public function assertPageAddressIsNot($page)
    {
        $this->assertSession()->addressNotEquals($this->locatePath($page));
    }


    /**
     * @Given /^I load database$/
     */
    public function iLoadDatabase()
    {

        $this->loadFixtures($this->dataFixtures);
    }

    /**
     * @Given /^I add datafixture "(?P<datafixture>(?:[^"]|\\")*)"$/
     */
    public function iAddDatafixture($datafixture)
    {
        $this->dataFixtures[] = $datafixture;
    }

    /**
     * Load data fixture
     *
     * @param array $classNames
     * @param null  $omName
     *
     * @return ORMExecutor
     */
    protected function loadFixtures(array $classNames, $omName = null)
    {
        $container = $this->getContainer();

        /** @var Registry $registry */
        $registry = $container->get('doctrine');

        /** @var EntityManager $om */
        $om = $registry->getManager();
        $connection = $om->getConnection();
        $referenceRepository = new ProxyReferenceRepository($om);

        if ($connection->getDriver() instanceof SqliteDriver) {
            $params = $connection->getParams();
            if (isset($params['master'])) {
                $params = $params['master'];
            }

            $name = isset($params['path']) ? $params['path'] : (isset($params['dbname']) ? $params['dbname'] : false);
            if (!$name) {
                throw new \InvalidArgumentException("Connection does not contain a 'path' or 'dbname' parameter and cannot be dropped.");
            }

            if (!isset(self::$cachedMetadatas[$omName])) {
                self::$cachedMetadatas[$omName] = $om->getMetadataFactory()->getAllMetadata();
            }
            $metadatas = self::$cachedMetadatas[$omName];

            if ($container->getParameter('chaplean_unit.cache_sqlite_db')) {
                $backup = $container->getParameter('kernel.cache_dir') . '/test_' . md5(serialize($metadatas) . serialize($classNames)) . '.db';
                if (file_exists($backup) && file_exists($backup.'.ser') && $this->isBackupUpToDate($classNames, $backup)) {
                    $om->flush();
                    $om->clear();

                    $executor = new ORMExecutor($om);
                    $executor->setReferenceRepository($referenceRepository);
                    $executor->getReferenceRepository()->load($backup);

                    copy($backup, $name);

                    return $executor;
                }
            }

            // TODO: handle case when using persistent connections. Fail loudly?
            $schemaTool = new SchemaTool($om);
            $schemaTool->dropDatabase($name);
            if (!empty($metadatas)) {
                $schemaTool->createSchema($metadatas);
            }

            $executor = new ORMExecutor($om);
            $executor->setReferenceRepository($referenceRepository);
        }



        if (empty($executor)) {
            $purger = new ORMPurger();
            $executor = new ORMExecutor($om, $purger);

            $executor->setReferenceRepository($referenceRepository);
            $executor->purge();
        }

        $loader = new Loader($container);

        foreach ($classNames as $className) {
            $this->loadFixtureClass($loader, $className);
        }

        $executor->execute($loader->getFixtures(), true);

        if (isset($name) && isset($backup)) {
            $executor->getReferenceRepository()->save($backup);
            copy($name, $backup);
        }

        return $executor;
    }

    /**
     * Determine if the Fixtures that define a database backup have been
     * modified since the backup was made.
     *
     * @param array  $classNames The fixture classnames to check
     * @param string $backup     The fixture backup SQLite database file path
     *
     * @return bool TRUE if the backup was made since the modifications to the
     * fixtures; FALSE otherwise
     */
    private function isBackupUpToDate(array $classNames, $backup)
    {
        $backupLastModifiedDateTime = new \DateTime();
        $backupLastModifiedDateTime->setTimestamp(filemtime($backup));

        foreach ($classNames as &$className) {
            $fixtureLastModifiedDateTime = $this->getFixtureLastModified($className);
            if ($backupLastModifiedDateTime < $fixtureLastModifiedDateTime) {
                return false;
            }
        }

        return true;
    }

    /**
     * This function finds the time when the data blocks of a class definition
     * file were being written to, that is, the time when the content of the
     * file was changed.
     *
     * @param string $class The fully qualified class name of the fixture class to
     * check modification date on.
     *
     * @return \DateTime|null
     */
    private function getFixtureLastModified($class)
    {
        $lastModifiedDateTime = null;

        $reflClass = new \ReflectionClass($class);
        $classFileName = $reflClass->getFileName();

        if (file_exists($classFileName)) {
            $lastModifiedDateTime = new \DateTime();
            $lastModifiedDateTime->setTimestamp(filemtime($classFileName));
        }

        return $lastModifiedDateTime;
    }

    /**
     * Load a data fixture class.
     *
     * @param Loader $loader
     * @param string $className
     *
     * @return void
     */
    private function loadFixtureClass($loader, $className)
    {
        $fixture = new $className();

        if ($loader->hasFixture($fixture)) {
            unset($fixture);
            return;
        }

        $loader->addFixture($fixture);

        if ($fixture instanceof DependentFixtureInterface) {
            foreach ($fixture->getDependencies() as $dependency) {
                $this->loadFixtureClass($loader, $dependency);
            }
        }
    }
}