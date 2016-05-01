<?php

namespace Tests\Chaplean\Bundle\UnitBundle;

use Chaplean\Bundle\UnitBundle\Test\LogicalTest;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * LogicalTestTest.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.com)
 * @since     3.0.0
 */
class LogicalTestTest extends LogicalTest
{
    /**
     * @return void
     */
    public static function setUpBeforeClass()
    {
        parent::loadStaticFixtures(array());
    }

    /**
     * @return void
     */
    public function setUp()
    {
    }

    /**
     * @return void
     */
    public function testInstanceLogicalTest()
    {
        $logicalTest = new LogicalTest();

        $this->assertInstanceOf(ContainerInterface::class, $logicalTest->getContainer());
        $this->assertInstanceOf(EntityManager::class, $logicalTest->getManager());
        $this->assertEquals('', $logicalTest->getNamespace());
    }

    /**
     * @return void
     */
    public function testLoadDefaultFixtures()
    {
        $logicalTest = new LogicalTest();
        $logicalTest->setUp();

        $this->assertCount(0, $logicalTest->getManager()->getRepository('ChapleanUnitBundle:Client')->findAll());

        $logicalTest->setNamespaceFixtures('Chaplean\Bundle\UnitBundle\\');
        $logicalTest->setUpBeforeClass();
        
        $this->assertCount(1, $logicalTest->getManager()->getRepository('ChapleanUnitBundle:Client')->findAll());
        $this->assertCount(3, $logicalTest->getManager()->getRepository('ChapleanUnitBundle:Status')->findAll());

        $logicalTest->tearDown();
    }

    /**
     * @return void
     */
    public function testLoadPartialFixturesWithoutManager()
    {
        $logicalTest = new LogicalTest();
        $logicalTest->setUp();

        $logicalTest->setNamespaceFixtures('Chaplean\Bundle\UnitBundle\\');
        $logicalTest->loadPartialFixtures(array(
            'Chaplean\Bundle\UnitBundle\DataFixtures\Liip\LoadProviderData'
        ));

        $this->assertCount(1, $logicalTest->getManager()->getRepository('ChapleanUnitBundle:Provider')->findAll());

        $logicalTest->tearDown();
    }

    /**
     * @return void
     */
    public function testLoadFixturesByContext()
    {
        $logicalTest = new LogicalTest();
        $logicalTest->setUp();

        $logicalTest->setNamespaceFixtures('Chaplean\Bundle\UnitBundle\\');
        $logicalTest->loadFixturesByContext('DefaultData');

        $this->assertCount(1, $logicalTest->getManager()->getRepository('ChapleanUnitBundle:Client')->findAll());
        $this->assertCount(3, $logicalTest->getManager()->getRepository('ChapleanUnitBundle:Status')->findAll());

        $logicalTest->tearDown();
    }
}
