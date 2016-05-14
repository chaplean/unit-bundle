<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Driver;

use Chaplean\Bundle\UnitBundle\Test\LogicalTest;
use Chaplean\Bundle\UnitBundle\Utility\Driver\SqliteUtilityDriver;
use Doctrine\DBAL\DriverManager;

/**
 * SqliteUtilityDriverTest.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.com)
 * @since     3.0.0
 */
class SqliteUtilityDriverTest extends LogicalTest
{
    private $params;

    /**
     * @return void
     */
    public static function setUpBeforeClass()
    {
    }

    /**
     * @return void
     */
    public function setUp()
    {
        $this->params = array(
            'driver'        => 'pdo_sqlite',
            'host'          => '127.0.0.1',
            'port'          => '3306',
            'path'          => $this->getContainer()->getParameter('kernel.cache_dir') . '/test.db',
            'charset'       => 'UTF8',
            'serverVersion' => '5.5',
        );
    }


    /**
     * @return void
     */
    public function testGetFixtureLastModified()
    {
        $date = new \DateTime('2016-05-10 00:00:00');
        $file = new \ReflectionClass($this);
        touch($file->getFileName(), $date->getTimestamp());

        $lastUpdate = SqliteUtilityDriver::getFixtureLastModified($this);

        $this->assertEquals($date, $lastUpdate);
    }

    /**
     * @return void
     */
    public function testIsBackupUpToDate()
    {
        $date = new \DateTime('-3 days');
        touch(__FILE__, $date->getTimestamp());

        $this->assertFalse(SqliteUtilityDriver::isBackupUpToDate(array('Chaplean\Bundle\UnitBundle\DataFixtures\Liip\LoadProviderData'), __FILE__));
    }

    /**
     * @return void
     */
    public function testExistWithOlderDatabase()
    {
        $connection = DriverManager::getConnection($this->params);
        $fileDatabse = $connection->getParams()['path'];
        $date = new \DateTime('-3 days');
        touch($fileDatabse, $date->getTimestamp());

        $this->assertFalse(SqliteUtilityDriver::exist($connection, array('Chaplean\Bundle\UnitBundle\DataFixtures\Liip\LoadProviderData')));
    }
}
