<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Utility\NamespaceUtility;
use Composer\Autoload\ClassLoader;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class NamespaceUtilityTest.
 *
 * @package   Tests\Chaplean\Bundle\UnitBundle\Utility
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2016 Chaplean (https://www.chaplean.coop)
 * @since     4.1.0
 */
class NamespaceUtilityTest extends MockeryTestCase
{
    /**
     * @var KernelInterface|\Mockery\MockInterface
     */
    private $kernelMock;

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
        $this->kernelMock = \Mockery::mock(KernelInterface::class);
        $this->namespaceUtility = new NamespaceUtility($this->kernelMock);
    }

    /**
     * @covers                   \Chaplean\Bundle\UnitBundle\Utility\NamespaceUtility::__construct
     * @covers                   \Chaplean\Bundle\UnitBundle\Utility\NamespaceUtility::getClassNamesByContext()
     *
     * @return void
     * @throws \Exception
     */
    public function testGetClassNamesByContextFail()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('\'Foo\Bar\' namespace is not available. Check \'chaplean_unit.data_fixtures_namespace\' parameter !');

        $this->kernelMock->shouldReceive('getProjectDir')
            ->once()
            ->andReturn(__DIR__ . '/../../');

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
        $this->kernelMock->shouldReceive('getProjectDir')
            ->once()
            ->andReturn(__DIR__ . '/../../');

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
        $this->kernelMock->shouldReceive('getProjectDir')
            ->once()
            ->andReturn(__DIR__ . '/../../');

        $path = $this->namespaceUtility->getBundlePath('Chaplean\Bundle\UnitBundle\\');

        $this->assertStringEndsWith('/vendor/composer/../../', $path);
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\NamespaceUtility::getBundlePath()
     *
     * @return void
     * @throws \ReflectionException
     */
    public function testGetBundlePathNameEmpty()
    {
        $this->expectException(\ReflectionException::class);

        $this->kernelMock->shouldReceive('getProjectDir')
            ->once()
            ->andReturn(__DIR__ . '/../../');

        $path = $this->namespaceUtility->getBundlePath('');

        $this->assertEquals('', $path);
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\NamespaceUtility::getAutoload()
     *
     * @return void
     */
    public function testGetAutoloadInstance()
    {
        $this->kernelMock->shouldReceive('getProjectDir')
            ->once()
            ->andReturn(__DIR__ . '/../../');

        $autoload = $this->namespaceUtility->getAutoload();

        $this->assertInstanceOf(ClassLoader::class, $autoload);
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\NamespaceUtility::getAutoload()
     *
     * @return void
     */
    public function testGetAutoloadInstanceClassMap()
    {
        $this->kernelMock->shouldReceive('getProjectDir')
            ->once()
            ->andReturn(__DIR__ . '/../../');

        $autoload = $this->namespaceUtility->getAutoload();

        $this->assertNotEmpty($autoload->getClassMap());
    }
}
