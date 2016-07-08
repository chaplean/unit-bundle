<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Driver;

use Chaplean\Bundle\UnitBundle\Test\LogicalTest;
use Chaplean\Bundle\UnitBundle\Utility\Driver\MySqlUtilityDriver;
use Doctrine\DBAL\DriverManager;

/**
 * MySqlUtilityDriverTest.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.com)
 * @since     3.0.0
 */
class MySqlUtilityDriverTest extends LogicalTest
{
    /**
     * @var array
     */
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
            'driver'        => 'pdo_mysql',
            'host'          => $this->getContainer()->getParameter('database_host'),
            'port'          => $this->getContainer()->getParameter('database_port'),
            'dbname'        => 'test_database_doesnt_exist',
            'user'          => $this->getContainer()->getParameter('database_user'),
            'password'      => $this->getContainer()->getParameter('database_password'),
            'charset'       => 'UTF8',
            'serverVersion' => '5.6',
        );
    }

    /**
     * @return void
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testCreateDatabase()
    {
        $connection = DriverManager::getConnection($this->params);

        MySqlUtilityDriver::createDatabase($connection);
        $this->assertTrue(MySqlUtilityDriver::exist($connection));
    }

    /**
     * @return void
     * @throws \Doctrine\DBAL\DBALException
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->params['dbname']);
        DriverManager::getConnection($this->params)->executeQuery('DROP DATABASE test_database_doesnt_exist');
    }
}
