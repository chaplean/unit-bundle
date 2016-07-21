<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Test\LogicalTestCase;
use Chaplean\Bundle\UnitBundle\Utility\NamespaceUtility;

/**
 * Class NamespaceUtilityTest.
 *
 * @package   Tests\Chaplean\Bundle\UnitBundle\Utility
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.com)
 * @since     4.1.0
 */
class NamespaceUtilityTest extends LogicalTestCase
{
    /**
     * @return void
     * @expectedException \Exception
     * @expectedExceptionMessage 'Foo\Bar' namespace is not available. Check 'data_fixtures_namespace' parameter !
     */
    public function testGetClassNamesByContext()
    {
        NamespaceUtility::getClassNamesByContext('Foo\Bar', 'DefaultData');
    }
}
