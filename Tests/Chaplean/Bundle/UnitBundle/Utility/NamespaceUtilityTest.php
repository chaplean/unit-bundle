<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Utility\NamespaceUtility;
use Composer\Autoload\ClassLoader;
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
     * @covers \Chaplean\Bundle\UnitBundle\Utility\NamespaceUtility::getClassNamesByContext()
     *
     * @return void
     * @expectedException \Exception
     * @expectedExceptionMessage 'Foo\Bar' namespace is not available. Check 'data_fixtures_namespace' parameter !
     */
    public function testGetClassNamesByContextFail()
    {
        NamespaceUtility::getClassNamesByContext('Foo\Bar');
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\NamespaceUtility::getClassNamesByContext()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\NamespaceUtility::getNamespacePathDataFixtures()
     *
     * @return void
     * @throws \Exception
     */
    public function testGetClassNamesByContext()
    {
        $classNames = NamespaceUtility::getClassNamesByContext('Chaplean\Bundle\UnitBundle\\');

        $this->assertCount(4, $classNames);
    }

    /**
     * @return void
     */
    public function testGetBundleClassNameWithBundle()
    {
        $name = NamespaceUtility::getBundleClassName('Chaplean\Bundle\UnitBundle\\');

        $this->assertEquals('ChapleanUnitBundle', $name);
    }

    /**
     * @return void
     */
    public function testGetBundleClassNameEmpty()
    {
        $name = NamespaceUtility::getBundleClassName('');

        $this->assertEquals('', $name);
    }

    /**
     * @return void
     */
    public function testGetBundleClassNameApp()
    {
        $name = NamespaceUtility::getBundleClassName('App');

        $this->assertEquals('', $name);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testGetBundlePathNameWithBundle()
    {
        $path = NamespaceUtility::getBundlePath('Chaplean\Bundle\UnitBundle\\');

        $this->assertEquals('/var/www/symfony/vendor/composer/../../', $path);
    }

    /**
     * @return void
     * @expectedException \ReflectionException
     */
    public function testGetBundlePathNameEmpty()
    {
        $path = NamespaceUtility::getBundlePath('');

        $this->assertEquals('', $path);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testGetBundlePathNameApp()
    {
        $path = NamespaceUtility::getBundlePath('App\\');

        $this->assertEquals('/var/www/symfony/vendor/composer/../../', $path);
    }

    /**
     * @return void
     */
    public function testGetAutoloadInstance() {
        $autoload = NamespaceUtility::getAutoload();

        $this->assertInstanceOf(ClassLoader::class, $autoload);
    }

    /**
     * @return void
     */
    public function testGetAutoloadInstanceClassMap() {
        $autoload = NamespaceUtility::getAutoload();

        $this->assertNotEmpty($autoload->getClassMap());
    }
}
