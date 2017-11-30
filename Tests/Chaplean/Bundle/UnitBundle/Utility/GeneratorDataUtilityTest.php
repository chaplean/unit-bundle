<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Entity\Client;
use Chaplean\Bundle\UnitBundle\Entity\Product;
use Chaplean\Bundle\UnitBundle\Utility\GeneratorDataUtility;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * GeneratorDataUtilityTest.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.coop)
 * @since     3.0.0
 */
class GeneratorDataUtilityTest extends MockeryTestCase
{
    /**
     * @return void
     * @expectedException \Exception
     * @expectedExceptionMessage Reference 'ClassNotExist:Foo' not exist
     */
    public function testGetReferenceNotExist()
    {
        $generator = new GeneratorDataUtility(__DIR__ . '/../../../../../Resources/config/datafixtures.yml');

        $generator->getReference('ClassNotExist', 'Foo');
    }

    /**
     * @return void
     */
    public function testGetReferenceExist()
    {
        $generator = new GeneratorDataUtility(__DIR__ . '/../../../../../Resources/config/datafixtures.yml');

        $generator->hasReference('Chaplean\Bundle\UnitBundle\Entity\Client', 'name');
        $this->assertEquals(null, $generator->getReference('Chaplean\Bundle\UnitBundle\Entity\Client', 'name'));
    }

    /**
     * @return void
     *
     * @expectedException \Exception
     * @expectedExceptionMessage No definition load !
     */
    public function testClassDefinitionExistWithEmptyDefinition()
    {
        $mock = \Mockery::mock(GeneratorDataUtility::class)
            ->makePartial();

        /** @noinspection PhpUndefinedMethodInspection */
        $mock->classDefinitionExist('', '');
    }

    /**
     * @return void
     */
    public function testHasReference()
    {
        $mock = $this->getMockBuilder('Chaplean\Bundle\UnitBundle\Utility\GeneratorDataUtility')
            ->setConstructorArgs([__DIR__ . '/../../../../../Resources/config/datafixtures.yml'])
            ->setMethods(['classDefinitionExist'])
            ->getMock();

        $mock->expects($this->any())
            ->method('classDefinitionExist')
            ->willThrowException(new \Exception());

        $this->assertFalse($mock->hasReference('Chaplean\Bundle\UnitBundle\Entity\Client', 'name'));
    }

    /**
     * @return void
     */
    public function testNewGenerator()
    {
        $generator = new GeneratorDataUtility(null);

        $this->assertInstanceOf(GeneratorDataUtility::class, $generator);
    }

    /**
     * @return void
     */
    public function testGetDataWithDefaultDefinition()
    {
        $generator = new GeneratorDataUtility(null);

        $value = $generator->getData(Client::class, 'name');

        $this->assertNotEmpty($value);
    }

    /**
     * @return void
     */
    public function testGetDataWithCustomDefinition()
    {
        $generator = new GeneratorDataUtility(__DIR__ . '/../Resources/config/datafixtures_test.yml');

        $value = $generator->getData(Client::class, 'name');

        $this->assertNotEmpty($value);
        $this->assertEquals('A0B', $value);
    }

    /**
     * @return void
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Missing definition for entity ('Chaplean\Bundle\UnitBundle\Entity\Product')
     */
    public function testGetDataMissingClassDefinition()
    {
        $generator = new GeneratorDataUtility(__DIR__ . '/../Resources/config/datafixtures_test.yml');

        $generator->getData(Product::class, 'name');
    }

    /**
     * @return void
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Missing definition for required field ('email')
     */
    public function testGetDataMissingFieldDefinition()
    {
        $generator = new GeneratorDataUtility(__DIR__ . '/../Resources/config/datafixtures_test.yml');

        $generator->getData(Client::class, 'email');
    }

    /**
     * @return void
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Unvalid format in definition, 'properties' not found
     */
    public function testGetDataInvalidFormatDefinition()
    {
        $generator = new GeneratorDataUtility(__DIR__ . '/../Resources/config/datafixtures_invalid_format.yml');

        $generator->getData(Client::class, 'email');
    }

    /**
     * @return void
     */
    public function testGetDataWithUseCurentDefinition()
    {
        $generator = new GeneratorDataUtility(__DIR__ . '/../Resources/config/datafixtures_test.yml');

        $value = $generator->getData(Client::class, 'name');

        $this->assertNotEmpty($value);
        $this->assertEquals('A0B', $value);

        $value = $generator->getData(Client::class, 'name');

        $this->assertNotEmpty($value);
        $this->assertEquals('A1B', $value);
    }
}
