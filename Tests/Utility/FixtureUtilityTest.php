<?php

namespace Chaplean\Bundle\UnitBundle\Tests\Utility;

use Chaplean\Bundle\UnitBundle\Test\LogicalTest;
use Chaplean\Bundle\UnitBundle\Utility\FixtureUtility;

/**
 * FixtureUtilityTest.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     2.0.0
 */
class FixtureUtilityTest extends LogicalTest
{
    /**
     * @return void
     */
    public function testGenerateDataRandomArray()
    {
        $value = FixtureUtility::generateRandomData('array');

        $this->assertInternalType('array', $value);
        $this->assertCount(0, $value);
    }

    /**
     * @return void
     */
    public function testGenerateDataRandomInteger()
    {
        $value = FixtureUtility::generateRandomData('integer');

        $this->assertInternalType('int', $value);
        $this->assertLessThan(PHP_INT_MAX, $value);
    }

    /**
     * @return void
     */
    public function testGenerateDataRandomBigint()
    {
        $value = FixtureUtility::generateRandomData('bigint');

        $this->assertInternalType('int', $value);
        $this->assertLessThan(PHP_INT_MAX, $value);
    }

    /**
     * @return void
     */
    public function testGenerateDataRandomSmallint()
    {
        $value = FixtureUtility::generateRandomData('smallint');

        $this->assertInternalType('int', $value);
        $this->assertGreaterThanOrEqual(0, $value);
        $this->assertLessThanOrEqual(1, $value);
    }

    /**
     * @return void
     */
    public function testGenerateDataRandomBoolean()
    {
        $value = FixtureUtility::generateRandomData('boolean');

        $this->assertInternalType('bool', $value);
    }

    /**
     * @return void
     */
    public function testGenerateDataRandomDate()
    {
        $value = FixtureUtility::generateRandomData('date');

        $this->assertInstanceOf(\DateTime::class, $value);
    }

    /**
     * @return void
     */
    public function testGenerateDataRandomDateTime()
    {
        $value = FixtureUtility::generateRandomData('datetime');

        $this->assertInstanceOf(\DateTime::class, $value);
    }

    /**
     * @return void
     */
    public function testGenerateDataRandomDecimal()
    {
        $value = FixtureUtility::generateRandomData('decimal');

        $this->assertInternalType('float', $value);
        $this->assertNotEquals(0, $value - floor($value));
    }

    /**
     * @return void
     */
    public function testGenerateDataRandomFloat()
    {
        $value = FixtureUtility::generateRandomData('float');

        $this->assertInternalType('float', $value);
        $this->assertNotEquals(0, $value - floor($value));
    }

    /**
     * @return void
     */
    public function testGenerateDataRandomText()
    {
        $value = FixtureUtility::generateRandomData('text');

        $this->assertInternalType('string', $value);
        $this->assertRegExp('/[\w]+/', $value);
    }

    /**
     * @return void
     */
    public function testGenerateDataRandomString()
    {
        $value = FixtureUtility::generateRandomData('string');

        $this->assertInternalType('string', $value);
        $this->assertRegExp('/[\w]+/', $value);
    }

    /**
     * @return void
     */
    public function testGenerateDataRandomTypeDoesntExists()
    {
        $value = FixtureUtility::generateRandomData('type_doesnt_exist');

        $this->assertNull($value);
    }
}
