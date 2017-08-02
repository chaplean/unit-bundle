<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Test\LogicalTestCase;

/**
 * AbstractFixtures.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.coop)
 * @since     3.0.3
 */
class AbstractFixtureTest extends LogicalTestCase
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
     * @expectedException \Exception
     * @expectedExceptionMessage 'client-2' is not persisted !
     */
    public function testLoadFixturesWithReferenceNotPersist()
    {
        $this->loadPartialFixturesByContext('FixturesWithError');
    }

    /**
     * @return void
     */
    public function testEntityManagerStillAlive()
    {
        $this->assertEquals(spl_object_hash($this->getManager()), spl_object_hash($this->getContainer()->get('doctrine')->getManager()));
        $this->assertTrue($this->em->isOpen());
    }
}
