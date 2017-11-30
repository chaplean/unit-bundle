<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Utility\NamespaceUtility;
use PHPUnit\Framework\TestCase;

/**
 * Class NamespaceUtilityTest.
 *
 * @package   Tests\Chaplean\Bundle\UnitBundle\Utility
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.coop)
 * @since     4.1.0
 */
class NamespaceUtilityTest extends TestCase
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
