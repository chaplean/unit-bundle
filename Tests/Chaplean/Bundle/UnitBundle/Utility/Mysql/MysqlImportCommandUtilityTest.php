<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Utility\Mysql;

use Chaplean\Bundle\UnitBundle\Test\LogicalTestCase;
use Chaplean\Bundle\UnitBundle\Utility\Mysql\MysqlImportCommandUtility;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOSqlite\Driver;

/**
 * Class MysqlImportCommandUtilityTest.
 *
 * @author    Tom - Chaplean <tom@chaplean.com>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.com)
 * @since     4.2.0
 */
class MysqlImportCommandUtilityTest extends LogicalTestCase
{
    private static $connection;

    /**
     * @return void
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        // We need to use Sqlite connection even if we're testing mysql otherwise attempt to connect will fail
        $driver = new Driver();

        self::$connection = new Connection(
            array(
                'host'     => 'myhost',
                'port'     => 'myport',
                'user'     => 'myuser',
                'password' => 'mypassword',
                'path'     => 'mydatabase'
            ),
            $driver
        );
    }

    /**
     * @return void
     */
    public function testGetCommandLineArguments()
    {
        $mysqlDump = new MysqlImportCommandUtility(self::$connection, '');

        $this->assertEquals('-hmyhost -Pmyport -umyuser -pmypassword mydatabase', $mysqlDump->getCommandLineArguments());
    }

    /**
     * @return void
     */
    public function testGetCommandLine()
    {
        $mysqlDump = new MysqlImportCommandUtility(self::$connection, 'file.sql');

        $this->assertEquals('mysql -hmyhost -Pmyport -umyuser -pmypassword mydatabase < file.sql', $mysqlDump->getCommandLine());
    }

    /**
     * @return void
     */
    public function testExec()
    {
        $mysqlDump = new MysqlImportCommandUtility(self::$connection, '/dev/null');

        $this->assertEquals(1, $mysqlDump->exec());
    }
}
