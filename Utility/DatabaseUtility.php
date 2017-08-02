<?php

namespace Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Utility\Driver\MySqlUtilityDriver;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\PDOMySql\Driver as MySqlDriver;
use Doctrine\DBAL\Driver\PDOSqlite\Driver as SqliteDriver;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * DatabaseUtility.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.coop)
 * @since     2.0.0
 */
class DatabaseUtility
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private static $cachedMetadatas = array();

    /**
     * @var Driver
     */
    private $driver;

    /**
     * @var array
     */
    private $metadatas = null;

    /**
     * @var EntityManager
     */
    private $om;

    /**
     * @var EntityManager
     */
    private $tmpOm;

    /**
     * @var array
     */
    private $params;

    /**
     * @var string
     */
    private $hash;

    /**
     * @param EntityManager $om
     *
     * @return void
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public function cleanDatabase(EntityManager $om = null)
    {
        if ($om === null && $this->driver instanceof MySqlDriver) {
            $om = $this->om;
        }

        if ($om !== null) {
            $schemaTool = new SchemaTool($om);
            $schemaTool->dropDatabase();

            if (!empty($this->metadatas)) {
                $schemaTool->createSchema($this->metadatas);
            }
        }
    }

    /**
     * @param array $params
     *
     * @return void
     */
    public static function checkParams(array $params)
    {
        $name = isset($params['path']) ? $params['path'] : (isset($params['dbname']) ? $params['dbname'] : false);
        if (!$name) {
            throw new \InvalidArgumentException('Connection does not contain a \'path\' or \'dbname\' parameter and cannot be dropped.');
        }
    }

    /**
     * @param array $classNames
     *
     * @return boolean
     * @throws \Exception
     */
    public function exist(array $classNames = array())
    {
        if ($this->driver instanceof MySqlDriver) {
            return MySqlUtilityDriver::exist($this->om->getConnection());
        }
        
        throw new \Exception(get_class($this->driver) . ' not supported driver.');
    }

    /**
     * @param array     $classNames
     * @param Registry  $registry
     * @param Container $container
     *
     * @return void
     */
    public function initDatabase(array $classNames, Registry $registry, Container $container)
    {
        $this->om = $registry->getManager();
        self::checkParams($this->om->getConnection()->getParams());

        $this->container = $container;
        $this->metadatas = self::getMetadatas($this->om);
        $this->hash = md5(serialize($classNames) . serialize($this->metadatas));
        $this->driver = $this->om->getConnection()->getDriver();
    }

    /**
     * Create schema database
     *
     * @return void
     * @throws \Doctrine\ORM\Tools\ToolsException
     * @throws \Exception
     */
    public function createSchemaDatabase()
    {
        if ($this->driver instanceof MySqlDriver) {
            MySqlUtilityDriver::createDatabase($this->om->getConnection());
            $om = $this->om;
        } else {
            throw new \Exception(get_class($this->driver) . ' not supported driver.');
        }

        $schemaTool = new SchemaTool($om);
        $schemaTool->dropDatabase();

        if (!empty($this->metadatas)) {
            $schemaTool->createSchema($this->metadatas);
        }
    }

    /**
     * @return Driver
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * @param EntityManager $om
     *
     * @return mixed
     */
    private static function getMetadatas(EntityManager $om)
    {
        if (!isset(self::$cachedMetadatas['doctrine'])) {
            self::$cachedMetadatas['doctrine'] = $om->getMetadataFactory()->getAllMetadata();
            usort(
                self::$cachedMetadatas['doctrine'],
                function ($a, $b) {
                    return strcmp($a->name, $b->name);
                }
            );
        }
        return self::$cachedMetadatas['doctrine'];
    }

    /**
     * Get om.
     *
     * @return EntityManager
     */
    public function getOm()
    {
        return !empty($this->tmpOm) ? $this->tmpOm : $this->om;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Get params.
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set container.
     *
     * @param ContainerInterface $container
     *
     * @return DatabaseUtility
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }
}
