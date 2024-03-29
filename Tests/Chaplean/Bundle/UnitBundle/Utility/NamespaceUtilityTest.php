<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase;
use Chaplean\Bundle\UnitBundle\Utility\NamespaceUtility;
use Composer\Autoload\ClassLoader;

/**
 * Class NamespaceUtilityTest.
 *
 * @package   Tests\Chaplean\Bundle\UnitBundle\Utility
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2016 Chaplean (https://www.chaplean.coop)
 * @since     4.1.0
 */
class NamespaceUtilityTest extends FunctionalTestCase
{
    /**
     * @var NamespaceUtility
     */
    private $namespaceUtility;

    /**
     * @return void
     * @throws \Exception
     */
    public function setUp(): void
    {
        $this->namespaceUtility = new NamespaceUtility($this->getContainer()->get('kernel'));
    }
    
    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\NamespaceUtility::__construct
     * @covers \Chaplean\Bundle\UnitBundle\Utility\NamespaceUtility::getClassNamesByContext()
     *
     * @return void
     * @expectedException \Exception
     * @expectedExceptionMessage 'Foo\Bar' namespace is not available. Check 'data_fixtures_namespace' parameter !
     */
    public function testGetClassNamesByContextFail()
    {
        $this->namespaceUtility->getClassNamesByContext('Foo\Bar');
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
        $classNames = $this->namespaceUtility->getClassNamesByContext('Chaplean\Bundle\UnitBundle\\');

        $this->assertCount(4, $classNames);
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\NamespaceUtility::getBundleClassName()
     *
     * @return void
     */
    public function testGetBundleClassNameWithBundle()
    {
        $name = $this->namespaceUtility->getBundleClassName('Chaplean\Bundle\UnitBundle\\');

        $this->assertEquals('ChapleanUnitBundle', $name);
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\NamespaceUtility::getBundleClassName()
     *
     * @return void
     */
    public function testGetBundleClassNameEmpty()
    {
        $name = $this->namespaceUtility->getBundleClassName('');

        $this->assertEquals('', $name);
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\NamespaceUtility::getBundleClassName()
     *
     * @return void
     */
    public function testGetBundleClassNameApp()
    {
        $name = $this->namespaceUtility->getBundleClassName('App');

        $this->assertEquals('', $name);
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\NamespaceUtility::getBundlePath()
     *
     * @return void
     * @throws \ReflectionException
     */
    public function testGetBundlePathNameWithBundle()
    {
        $path = $this->namespaceUtility->getBundlePath('Chaplean\Bundle\UnitBundle\\');

        $this->assertStringEndsWith('/vendor/composer/../../', $path);
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\NamespaceUtility::getBundlePath()
     *
     * @return void
     * @expectedException \ReflectionException
     */
    public function testGetBundlePathNameEmpty()
    {
        $path = $this->namespaceUtility->getBundlePath('');

        $this->assertEquals('', $path);
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\NamespaceUtility::getBundlePath()
     *
     * @return void
     * @throws \ReflectionException
     */
    public function testGetBundlePathNameApp()
    {
        $path = $this->namespaceUtility->getBundlePath('App\\');

        $this->assertStringEndsWith('/vendor/composer/../../', $path);
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\NamespaceUtility::getAutoload()
     *
     * @return void
     */
    public function testGetAutoloadInstance() {
        $autoload = $this->namespaceUtility->getAutoload();

        $this->assertInstanceOf(ClassLoader::class, $autoload);
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\NamespaceUtility::getAutoload()
     *
     * @return void
     */
    public function testGetAutoloadInstanceClassMap() {
        $autoload = $this->namespaceUtility->getAutoload();

        $this->assertNotEmpty($autoload->getClassMap());
    }
}
