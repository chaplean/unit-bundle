<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Test\LogicalTestCase;
use Chaplean\Bundle\UnitBundle\Utility\DatabaseUtility;

/**
 * DatabaseUtilityTest.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.coop)
 * @since     3.0.0
 */
class DatabaseUtilityTest extends LogicalTestCase
{
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
