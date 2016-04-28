<?php

namespace Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Utility\Driver\MySqlUtilityDriver;
use Chaplean\Bundle\UnitBundle\Utility\Driver\SqliteUtilityDriver;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\PDOMySql\Driver as MySqlDriver;
use Doctrine\DBAL\Driver\PDOSqlite\Driver as SqliteDriver;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * DatabaseUtility.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     2.0.0
 */
class DatabaseUtility
{
    /**
     * @var array
     */
    private static $cachedMetadatas = array();
    
    /**
     * @var EntityManager[]
     */
    private static $cachedOm = array();

    /**
     * @var Driver
     */
    private $driver;

    /**
     * @var array
     */
    private $metadatas = null;

    /**
     * @var string
     */
    private $typeTest;

    /**
     * @var EntityManager
     */
    private $om;

    /**
     * @var array
     */
    private $params;

    /**
     * @var string
     */
    private $hash;

    /**
     * @return EntityManager
     * @throws \Doctrine\DBAL\DBALException
     */
    private function buildTmpOm()
    {
        $params = $this->om->getConnection()->getParams();
        if (isset($params['dbname'])) {
            $params['dbname'] .= '_' . $this->hash;
        } elseif (isset($params['path'])) {
            $params['path'] = (str_replace('.db', ('_' . $this->hash), $params['path']) . '.db');
        }

        /** @var Connection $tmpConnection */
        $tmpConnection = DriverManager::getConnection($params);

        return EntityManager::create($tmpConnection, $this->om->getConfiguration());
    }

    /**
     * @return void
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public function cleanDatabaseOrigin()
    {
        $schemaTool = new SchemaTool($this->om);
        $schemaTool->dropDatabase();
    }

    /**
     * @return void
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public function cleanDatabaseTemporary()
    {
        $schemaTool = new SchemaTool(self::$cachedOm[$this->hash]);
        $schemaTool->dropDatabase();

        if (!empty($this->metadatas)) {
            $schemaTool->createSchema($this->metadatas);
        }
    }

    /**
     * @param array $params
     *
     * @return void
     */
    private static function checkParams($params)
    {
        $name = isset($params['path']) ? $params['path'] : (isset($params['dbname']) ? $params['dbname'] : false);
        if (!$name) {
            throw new \InvalidArgumentException(
                "Connection does not contain a 'path' or 'dbname' parameter and cannot be dropped."
            );
        }
    }

    /**
     * @return boolean
     * @throws \Exception
     */
    public function exist()
    {
        switch (true) {
            case $this->driver instanceof SqliteDriver:
                return SqliteUtilityDriver::exist(self::$cachedOm[$this->hash]->getConnection());
                break;
            case $this->driver instanceof MySqlDriver:
                self::$cachedOm[$this->hash]->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
                return MySqlUtilityDriver::existDatabase(self::$cachedOm[$this->hash]->getConnection());
                break;
        }
        
        throw new \Exception(get_class($this->driver) . ' not supported driver.');
    }

    /**
     * @param array    $classNames
     * @param string   $typeTest
     * @param Registry $registry
     *
     * @return void
     */
    public function initDatabase($classNames, $typeTest, $registry)
    {
        $this->typeTest = $typeTest;
        $this->om = $registry->getManager();
        $this->metadatas = self::getMetadatas($this->om);
        $this->hash = md5(serialize($classNames) . serialize($this->metadatas));

        if (!isset(self::$cachedOm[$this->hash])) {
            /** @var Connection $orginalConnection */
            $orginalConnection = $this->om->getConnection();
            $params = $orginalConnection->getParams();
            self::checkParams($params);

            self::$cachedOm[$this->hash] = self::buildTmpOm();
        }
        $this->driver = self::$cachedOm[$this->hash]->getConnection()->getDriver();
    }

    /**
     * Create schema database
     *
     * @return void
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public function createSchemaDatabase()
    {
        switch (true) {
            case $this->driver instanceof SqliteDriver:
                break;
            case $this->driver instanceof MySqlDriver:
                MySqlUtilityDriver::createDatabase(self::$cachedOm[$this->hash]->getConnection());
        }
        
        $schemaToolOriginal = new SchemaTool($this->om);
        $schemaToolOriginal->dropDatabase();

        $schemaTool = new SchemaTool(self::$cachedOm[$this->hash]);
        $schemaTool->dropDatabase();

        if (!empty($this->metadatas)) {
            $schemaTool->createSchema($this->metadatas);
        }
    }

    /**
     * @param EntityManager $om
     *
     * @return mixed
     */
    private static function getMetadatas($om)
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
        return isset(self::$cachedOm[$this->hash]) ? self::$cachedOm[$this->hash] : $this->om;
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
     * @return void
     */
    public function moveDatabase()
    {
        switch (true) {
            case $this->driver instanceof SqliteDriver:
                break;
            case $this->driver instanceof MySqlDriver:
                MySqlUtilityDriver::moveDatabase(self::$cachedOm[$this->hash], $this->om);
        }
    }
}
