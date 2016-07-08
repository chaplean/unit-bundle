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
        self::loadStaticFixtures();
        parent::setUpBeforeClass();
    }

    /**
     * @return void
     */
    public function setUp()
    {
        $this->params = array(
            'driver'        => 'pdo_sqlite',
            'host'          => $this->getContainer()->getParameter('database_host'),
            'path'          => $this->getContainer()->getParameter('kernel.cache_dir') . '/test.db',
            'charset'       => 'UTF8'
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
        $connection = DriverManager::getConnection($this->params);
        $fileDatabase = $connection->getParams()['path'];

        $threeDaysAgo = new \DateTime('-3 days');
        $fourDaysAgo = new \DateTime('-4 days');

        touch($fileDatabase, $fourDaysAgo->getTimestamp());
        touch($this->getContainer()->getParameter('kernel.root_dir') . '/../DataFixtures/Liip/LoadProviderData.php', $threeDaysAgo->getTimestamp());

        $this->assertFalse(SqliteUtilityDriver::isBackupUpToDate(array('Chaplean\Bundle\UnitBundle\DataFixtures\Liip\LoadProviderData'), $fileDatabase));
    }

    /**
     * @return void
     */
    public function testExistWithOlderDatabase()
    {
        $connection = DriverManager::getConnection($this->params);
        $fileDatabase = $connection->getParams()['path'];

        $threeDaysAgo = new \DateTime('-3 days');
        $fourDaysAgo = new \DateTime('-4 days');

        touch($fileDatabase, $fourDaysAgo->getTimestamp());
        touch($this->getContainer()->getParameter('kernel.root_dir') . '/../DataFixtures/Liip/LoadProviderData.php', $threeDaysAgo->getTimestamp());

        $this->assertFalse(SqliteUtilityDriver::exist($connection, array('Chaplean\Bundle\UnitBundle\DataFixtures\Liip\LoadProviderData')));
    }
}
