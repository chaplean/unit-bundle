<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Entity\Client;
use Chaplean\Bundle\UnitBundle\Entity\Product;
use Chaplean\Bundle\UnitBundle\Entity\Status;
use Chaplean\Bundle\UnitBundle\Utility\GeneratorDataUtility;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;

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
     * @covers \Chaplean\Bundle\UnitBundle\Utility\GeneratorDataUtility::__construct
     * @covers \Chaplean\Bundle\UnitBundle\Utility\GeneratorDataUtility::getReference()
     *
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
     * @covers \Chaplean\Bundle\UnitBundle\Utility\GeneratorDataUtility::__construct
     * @covers \Chaplean\Bundle\UnitBundle\Utility\GeneratorDataUtility::hasReference()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\GeneratorDataUtility::getReference()
     *
     * @return void
     */
    public function testGetReferenceExist()
    {
        $generator = new GeneratorDataUtility(__DIR__ . '/../../../../../Resources/config/datafixtures.yml');

        $generator->hasReference('Chaplean\Bundle\UnitBundle\Entity\Client', 'name');

        $this->assertEquals(null, $generator->getReference('Chaplean\Bundle\UnitBundle\Entity\Client', 'name'));
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\GeneratorDataUtility::classDefinitionExist()
     *
     * @return void
     *
     * @expectedException \Exception
     * @expectedExceptionMessage No datafixtures definition loaded !
     */
    public function testClassDefinitionExistWithEmptyDefinition()
    {
        /** @var GeneratorDataUtility $mock */
        $mock = \Mockery::mock(GeneratorDataUtility::class)->makePartial();

        $mock->classDefinitionExist('', '');
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\GeneratorDataUtility::classDefinitionExist()
     *
     * @return void
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Missing entity definition 'Chaplean\Bundle\UnitBundle\Entity\Product'
     */
    public function testClassDefinitionExistMissingEntityDefinition()
    {
        $generator = new GeneratorDataUtility(__DIR__ . '/../Resources/config/datafixtures_test.yml');

        $generator->classDefinitionExist(Product::class, 'name');
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\GeneratorDataUtility::classDefinitionExist()
     *
     * @return void
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Missing field defintion 'email' in 'Chaplean\Bundle\UnitBundle\Entity\Client'
     */
    public function testClassDefinitionExistMissingFieldDefinition()
    {
        $generator = new GeneratorDataUtility(__DIR__ . '/../Resources/config/datafixtures_test.yml');

        $generator->classDefinitionExist(Client::class, 'email');
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\GeneratorDataUtility::classDefinitionExist()
     *
     * @return void
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid format for 'Chaplean\Bundle\UnitBundle\Entity\Client', 'properties' node is missing
     */
    public function testClassDefinitionExistInvalidDefinition()
    {
        $generator = new GeneratorDataUtility(__DIR__ . '/../Resources/config/datafixtures_invalid_format.yml');

        $generator->classDefinitionExist(Client::class, 'email');
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\GeneratorDataUtility::getFieldsDefined()
     *
     * @return void
     */
    public function testGetFieldsDefined()
    {
        $generator = new GeneratorDataUtility(__DIR__ . '/../../../../../Resources/config/datafixtures.yml');

        $definition = $generator->getFieldsDefined(Product::class);

        $this->assertContains('name', $definition);
        $this->assertContains('client', $definition);
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\GeneratorDataUtility::getFieldsDefined()
     *
     * @return void
     */
    public function testGetFieldsDefinedEntityNotDefined()
    {
        $generator = new GeneratorDataUtility(__DIR__ . '/../../../../../Resources/config/datafixtures.yml');

        $definition = $generator->getFieldsDefined(Status::class);

        $this->assertEmpty($definition);
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\GeneratorDataUtility::hasReference()
     *
     * @return void
     */
    public function testHasReference()
    {
        /** @var GeneratorDataUtility|MockInterface $mock */
        $mock = \Mockery::mock(GeneratorDataUtility::class, [__DIR__ . '/../../../../../Resources/config/datafixtures.yml'])->makePartial();

        $mock->shouldReceive('classDefinitionExist')->once()->andThrow(new \Exception());

        $this->assertFalse($mock->hasReference('Chaplean\Bundle\UnitBundle\Entity\Client', 'name'));
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\GeneratorDataUtility::__construct
     *
     * @return void
     */
    public function testNewGenerator()
    {
        $generator = new GeneratorDataUtility(null);

        $this->assertInstanceOf(GeneratorDataUtility::class, $generator);
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\GeneratorDataUtility::getData()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\GeneratorDataUtility::parseProperty()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\GeneratorDataUtility::classDefinitionExist()
     *
     * @return void
     */
    public function testGetDataWithDefaultDefinition()
    {
        $generator = new GeneratorDataUtility(null);

        $value = $generator->getData(Client::class, 'name');

        $this->assertNotEmpty($value);
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\GeneratorDataUtility::getData()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\GeneratorDataUtility::parseProperty()
     *
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
     * @covers \Chaplean\Bundle\UnitBundle\Utility\GeneratorDataUtility::getData()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\GeneratorDataUtility::parseProperty()
     *
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
