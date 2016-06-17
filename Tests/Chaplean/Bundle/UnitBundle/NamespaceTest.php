<?php

namespace Tests\Chaplean\Bundle\UnitBundle;

use Chaplean\Bundle\UnitBundle\Test\LogicalTest;

/**
 * NamespaceTest.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.com)
 * @since     2.2.0
 */
class NamespaceTest extends LogicalTest
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
    public function testDefaultNamespace()
    {
        $this->assertEquals('Chaplean\\Bundle\\UnitBundle\\', self::getNamespace());
    }

    /**
     * @return void
     */
    public function testSetNamespace()
    {
        self::setNamespaceFixtures('App\\Bundle\\RestBundle\\');
        $this->assertEquals('App\\Bundle\\RestBundle\\', self::getNamespace());
    }

    /**
     * @return void
     */
    public function testSetAndResetNamespace()
    {
        self::setNamespaceFixtures('App\\Bundle\\RestBundle\\');
        $this->assertEquals('App\\Bundle\\RestBundle\\', self::getNamespace());

        self::resetNamespaceFixtures();

        $this->assertEquals('Chaplean\\Bundle\\UnitBundle\\', self::getNamespace());
    }
}
