<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Test\LogicalTest;

/**
 * AbstractFixtures.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.com)
 * @since     3.0.3
 */
class AbstractFixtureTest extends LogicalTest
{
    /**
     * @return void
     */
    public static function setUpBeforeClass()
    {
    }

    /**
     * @return void
     * @expectedException \Exception
     * @expectedExceptionMessage 'client-2' is not persisted !
     */
    public function testLoadFixturesWithReferenceNotPersist()
    {
        $this->loadPartialFixturesByContext('FixturesWithError');
    }
}
