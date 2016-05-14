<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Driver;

use Chaplean\Bundle\UnitBundle\Test\LogicalTest;
use Chaplean\Bundle\UnitBundle\Utility\Driver\SqliteUtilityDriver;

/**
 * SqliteUtilityDriverTest.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.com)
 * @since     3.0.0
 */
class SqliteUtilityDriverTest extends LogicalTest
{
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
//    /**
//     * @return void
//     */
//    public function testGetFixtureLastModifiedWithoutFileClassDoesntExist()
//    {
//        $builder = new MockBuilder();
//        $builder->setNamespace('Chaplean\Bundle\UnitBundle\Utility\Driver')
//            ->setName('file_exists')
//            ->setFunction(
//                function () {
//                    return false;
//                }
//            );
//        $mock = $builder->build();
//
//        $mock->enable();
//        $a = __NAMESPACE__;
//        $lastUpdate = SqliteUtilityDriver::getFixtureLastModified($this);
//        $mock->disable();
//
//        $this->assertNull($lastUpdate);
//    }

//    public function testIsBackupUpToDate()
//    {
//        $latest = new \DateTime('+2 months');
//
//        $mock = \Mockery::mock(SqliteUtilityDriver::class)
//            ->shouldReceive('getFixtureLastModified')
//            ->andReturn($latest)->getMock();
//
//        $this->assertFalse($mock->isBackupUpToDate(array('Chaplean\Bundle\UnitBundle\DataFixtures\Liip\LoadProviderData'), __FILE__));
//    }
}
