<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Entity\Client;
use Chaplean\Bundle\UnitBundle\Entity\Product;
use Chaplean\Bundle\UnitBundle\Test\LogicalTest;
use Chaplean\Bundle\UnitBundle\Utility\GeneratorData;

/**
 * GeneratorDataTest.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     2.0.0
 */
class GeneratorDataTest extends LogicalTest
{

    /**
     * @return void
     */
    public function testNewGenerator()
    {
        $generator = new GeneratorData(null);

        $this->assertInstanceOf(GeneratorData::class, $generator);
    }

    /**
     * @return void
     */
    public function testGetDataWithDefaultDefinition()
    {
        $generator = new GeneratorData(null);

        $value = $generator->getData(Client::class, 'name');

        $this->assertNotEmpty($value);
    }

    /**
     * @return void
     */
    public function testGetDataWithCustomDefinition()
    {
        $generator = new GeneratorData(__DIR__ . '/config/datafixtures_test.yml');

        $value = $generator->getData(Client::class, 'name');

        $this->assertNotEmpty($value);
        $this->assertEquals('A0B', $value);
    }

    /**
     * @return void
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Missing defintion for entity ('Chaplean\Bundle\UnitBundle\Entity\Product')
     */
    public function testGetDataMissingClassDefinition()
    {
        $generator = new GeneratorData(__DIR__ . '/config/datafixtures_test.yml');

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
        $generator = new GeneratorData(__DIR__ . '/config/datafixtures_test.yml');

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
        $generator = new GeneratorData(__DIR__ . '/config/datafixtures_invalid_format.yml');

        $generator->getData(Client::class, 'email');
    }

    /**
     * @return void
     */
    public function testGetDataWithUseCurentDefinition()
    {
        $generator = new GeneratorData(__DIR__ . '/config/datafixtures_test.yml');

        $value = $generator->getData(Client::class, 'name');

        $this->assertNotEmpty($value);
        $this->assertEquals('A0B', $value);

        $value = $generator->getData(Client::class, 'name');

        $this->assertNotEmpty($value);
        $this->assertEquals('A1B', $value);
    }
}
