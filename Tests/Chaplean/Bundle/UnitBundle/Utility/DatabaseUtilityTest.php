<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Test\LogicalTest;
use Chaplean\Bundle\UnitBundle\Utility\DatabaseUtility;

/**
 * DatabaseUtilityTest.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.com)
 * @since     3.0.0
 */
class DatabaseUtilityTest extends LogicalTest
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
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Connection does not contain a 'path' or 'dbname' parameter and cannot be dropped.
     */
    public function testCheckParams()
    {
        $database = new DatabaseUtility();

        $params = array();

        $database->checkParams($params);
    }
}
