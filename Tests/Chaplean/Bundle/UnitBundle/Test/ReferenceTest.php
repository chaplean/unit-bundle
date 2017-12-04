<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Test;

use Chaplean\Bundle\UnitBundle\Utility\Reference;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\InvalidArgumentException;

/**
 * ReferenceTest.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.coop)
 * @since     2.0.0
 */
class ReferenceTest extends TestCase
{
    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Reference::__construct
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Reference::buildReference()
     *
     * @return void
     */
    public function testInstanciateReference()
    {
        $reference = new DummyReference('@user');

        $this->assertEquals('only', $reference->getType());
        $this->assertEquals('user', $reference->getKey());
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Reference::__construct
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Reference::getReferenceKey()
     *
     * @return void
     */
    public function testGetReferenceKey()
    {
        $reference = new DummyReference('@user');

        $this->assertEquals('user', $reference->getReferenceKey());
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Reference::__construct
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Reference::buildReference()
     *
     * @return void
     */
    public function testInstanciateReferenceWithInterval()
    {
        $reference = new DummyReference('@user<1, 5>');

        $this->assertEquals('interval', $reference->getType());
        $this->assertEquals('user', $reference->getKey());
        $this->assertEquals(1, $reference->getMin());
        $this->assertEquals(5, $reference->getMax());
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Reference::__construct
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Reference::getReferenceKey()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Reference::getReferenceInterval()
     *
     * @return void
     */
    public function testGetReferenceInterval()
    {
        $reference = new DummyReference('@user<1, 3>');

        $this->assertEquals('user1', $reference->getReferenceKey());
        $this->assertEquals('user2', $reference->getReferenceKey());
        $this->assertEquals('user3', $reference->getReferenceKey());
        $this->assertEquals('user1', $reference->getReferenceKey());
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Reference::__construct
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Reference::buildReference()
     *
     * @return void
     */
    public function testInstanciateReferenceWithArray()
    {
        $reference = new DummyReference('@user-[1, 3, 5]');

        $this->assertEquals('array', $reference->getType());
        $this->assertEquals('user-', $reference->getKey());
        $this->assertEquals(['1', '3', '5'], $reference->getValues());
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Reference::__construct
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Reference::getReferenceKey()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Reference::getReferenceArray()
     *
     * @return void
     */
    public function testGetReferenceArray()
    {
        $reference = new DummyReference('@user-[1, 3, 5]');

        $this->assertEquals('user-1', $reference->getReferenceKey());
        $this->assertEquals('user-3', $reference->getReferenceKey());
        $this->assertEquals('user-5', $reference->getReferenceKey());
        $this->assertEquals('user-1', $reference->getReferenceKey());
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Reference::__construct
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Reference::buildReference
     *
     * @return void
     */
    public function testGetReferenceNew()
    {
        $reference = new DummyReference('@new()');

        $this->assertNull($reference->getReferenceKey());
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Reference::__construct
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Reference::buildReference
     *
     * @return void
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid definition reference '@user-[]'
     */
    public function testGetReferenceInvalidArray()
    {
        new DummyReference('@user-[]');
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Reference::__construct
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Reference::buildReference
     *
     * @return void
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid definition reference '@user-<>'
     */
    public function testGetReferenceInvalidInterval()
    {
        new DummyReference('@user-<>');
    }
}
class DummyReference extends Reference
{
    /**
     * Get key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get index.
     *
     * @return integer
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Get min.
     *
     * @return integer
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * Get max.
     *
     * @return integer
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * Get values.
     *
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }
}
