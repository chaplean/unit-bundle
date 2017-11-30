<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Utility\Mysql;

use Chaplean\Bundle\UnitBundle\Test\LogicalTestCase;
use Chaplean\Bundle\UnitBundle\Utility\Mysql\MysqlImportCommandUtility;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOSqlite\Driver;
use PHPUnit\Framework\TestCase;

/**
 * Class MysqlImportCommandUtilityTest.
 *
 * @author    Tom - Chaplean <tom@chaplean.coop>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.coop)
 * @since     4.2.0
 */
class MysqlImportCommandUtilityTest extends TestCase
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
            [
                'host'     => 'myhost',
                'port'     => 'myport',
                'user'     => 'myuser',
                'password' => 'mypassword',
                'path'     => 'mydatabase'
            ],
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
