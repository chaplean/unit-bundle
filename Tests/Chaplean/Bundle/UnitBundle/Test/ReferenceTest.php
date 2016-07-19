<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Test;

use Chaplean\Bundle\UnitBundle\Test\LogicalTestCase;
use Chaplean\Bundle\UnitBundle\Utility\Reference;
use Symfony\Component\Process\Exception\InvalidArgumentException;

/**
 * ReferenceTest.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     2.0.0
 */
class ReferenceTest extends LogicalTestCase
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
     */
    public function testInstanciateReference()
    {
        $reference = new DummyReference('@user');

        $this->assertEquals('only', $reference->getType());
        $this->assertEquals('user', $reference->getKey());
    }

    /**
     * @return void
     */
    public function testGetReferenceKey()
    {
        $reference = new DummyReference('@user');

        $this->assertEquals('user', $reference->getReferenceKey());
    }

    /**
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
     * @return void
     */
    public function testInstanciateReferenceWithArray()
    {
        $reference = new DummyReference('@user-[1, 3, 5]');

        $this->assertEquals('array', $reference->getType());
        $this->assertEquals('user-', $reference->getKey());
        $this->assertEquals(array('1', '3', '5'), $reference->getValues());
    }

    /**
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
     * @return void
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid definition reference '@user-[]'
     */
    public function testGetReferenceInvalidArray()
    {
        new DummyReference('@user-[]');
    }

    /**
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
