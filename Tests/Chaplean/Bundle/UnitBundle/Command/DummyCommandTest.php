<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Test\Command;

use Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase;
use Chaplean\Bundle\UnitBundle\Test\LogicalTestCase;

/**
 * Class DummyCommandTest.
 *
 * @author    Tom - Chaplean <tom@chaplean.coop>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.coop)
 * @since     1.0.0
 */
class DummyCommandTest extends FunctionalTestCase
{
    /**
     * @return void
     */
    public function testCommandHasSameKernel()
    {
        $kernelHash = !is_null(static::$kernel) ? spl_object_hash(static::$kernel) : null;

        $this->runCommand('list');

        $newKernelHash = !is_null(static::$kernel) ? spl_object_hash(static::$kernel) : null;

        $this->assertEquals($kernelHash, $newKernelHash);
    }
}
