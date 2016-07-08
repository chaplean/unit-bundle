<?php

namespace Tests\Chaplean\Bundle\UnitBundle;

use Chaplean\Bundle\UnitBundle\Test\LogicalTest;
use Chaplean\Bundle\UnitBundle\Utility\GeneratorData;

/**
 * GeneratorDataTest.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.com)
 * @since     3.0.0
 */
class GeneratorDataTest extends LogicalTest
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
     * @expectedException \Exception
     * @expectedExceptionMessage Reference 'ClassNotExist:Foo' not exist
     */
    public function testGetReferenceNotExist()
    {
        $generator = new GeneratorData(__DIR__ . '/../../../../Resources/config/datafixtures.yml');

        $generator->getReference('ClassNotExist', 'Foo');
    }

    /**
     * @return void
     */
    public function testGetReferenceExist()
    {
        $generator = new GeneratorData(__DIR__ . '/../../../../Resources/config/datafixtures.yml');

        $generator->hasReference('Chaplean\Bundle\UnitBundle\Entity\Client', 'name');
        $this->assertEquals(null, $generator->getReference('Chaplean\Bundle\UnitBundle\Entity\Client', 'name'));
    }

    /**
     * @return void
     * @expectedExceptionMessage No definition load !
     */
    public function testClassDefinitionExistWithEmptyDefinition()
    {
        $mock = $this->getMockBuilder(GeneratorData::class)
                ->disableOriginalConstructor()
                ->getMock();

        /** @noinspection PhpUndefinedMethodInspection */
        $mock->classDefinitionExist('', '');
    }

    /**
     * @return void
     */
    public function testHasReference()
    {
        $mock = $this->getMockBuilder('Chaplean\Bundle\UnitBundle\Utility\GeneratorData')
            ->setConstructorArgs(array(__DIR__ . '/../../../../Resources/config/datafixtures.yml'))
            ->setMethods(array('classDefinitionExist'))
            ->getMock();

        $mock->expects($this->any())
            ->method('classDefinitionExist')
            ->willThrowException(new \Exception());

        $this->assertFalse($mock->hasReference('Chaplean\Bundle\UnitBundle\Entity\Client', 'name'));
    }
}
