<?php
/**
 * FunctionalTest.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     0.1.0
 */

namespace Chaplean\Bundle\UnitBundle\Tests;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;

use Doctrine\DBAL\Driver\PDOSqlite\Driver as SqliteDriver;


class FunctionalTest extends \PHPUnit_Extensions_Selenium2TestCase
{
    /**
     * @var Container
     */
    static $container;

    /**
     * @var EntityManager
     */
    protected $em;

    static $cachedMetadatas = array();

    /**
     * @var string
     */
    protected $baseUrl;

    public static function setUpBeforeClass()
    {
        require_once "{$_SERVER['KERNEL_DIR']}/AppKernel.php";
        $kernel = new \AppKernel('test', true);
        $kernel->boot();

        self::$container = $kernel->getContainer();
    }


    /**
     * SetUp default for test selenium
     *
     * @return void
     */
    protected function setUp()
    {
        $this->baseUrl = self::$container->getParameter('base_url_selenium');
        $this->em = self::$container->get('doctrine')->getManager();

        $this->setBrowser('opera');
        $this->setBrowserUrl($this->baseUrl);
    }

    /**
     * Load data fixture
     *
     * @param array $classNames
     * @param null  $omName
     */
    protected function loadFixtures(array $classNames, $omName = null)
    {
        $loader = new Loader();

        /** @var Registry $registry */
        $registry = self::$container->get('doctrine');

        /** @var EntityManager $om */
        $om = $registry->getManager();
        $connection = $om->getConnection();

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

        }

        foreach ($classNames as $dataFixture) {
            $loader->addFixture(new $dataFixture());
        }

        $purger = new ORMPurger();
        $excutor = new ORMExecutor($om);

        $excutor->execute($loader->getFixtures(), true);
    }
}
