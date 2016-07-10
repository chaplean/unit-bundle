<?php

namespace Tests\Chaplean\Bundle\UnitBundle;

use Chaplean\Bundle\UnitBundle\Entity\Client;
use Chaplean\Bundle\UnitBundle\Test\LogicalTest;
use Chaplean\Bundle\UnitBundle\Utility\FixtureUtility;
use Doctrine\ORM\EntityManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * LogicalTestTest.php.
 *
 * @author                 Valentin - Chaplean <valentin@chaplean.com>
 * @copyright              2014 - 2016 Chaplean (http://www.chaplean.com)
 * @since                  3.0.0
 *
 * @backupStaticAttributes disabled
 */
class LogicalTestTest extends WebTestCase
{
    /**
     * @return void
     */
    public function testConstructorLogicalTest()
    {
        $logicalTest = new LogicalTest();

        $this->assertInstanceOf(ContainerInterface::class, $logicalTest->getContainer());
        $this->assertInstanceOf(FixtureUtility::class, $logicalTest->getFixtureUtility());
        $this->assertNull($logicalTest->getManager());
    }

    /**
     * @expectedException \Exception
     * @return void
     */
    public function testGetUndefinedProperty()
    {
        $logicalTest = new LogicalTest();

        /** @noinspection PhpUndefinedFieldInspection */
        $logicalTest->notDefined;
    }

    /**
     * @return void
     */
    public function testSetUpBeforeClassInitializeManager()
    {
        $logicalTest = new LogicalTest();
        $logicalTest->setNamespaceFixtures('Chaplean\Bundle\UnitBundle\\');
        $logicalTest->setUpBeforeClass();

        $this->assertInstanceOf(EntityManager::class, $logicalTest->getManager());
    }

    /**
     * @return void
     */
    public function testLoadDefaultFixtures()
    {
        $logicalTest = new LogicalTest();
        $logicalTest->setNamespaceFixtures('Chaplean\Bundle\UnitBundle\\');
        $logicalTest->setUpBeforeClass();
        $logicalTest->setUp();

        $this->assertCount(
            1,
            $logicalTest->getManager()
                ->getRepository('ChapleanUnitBundle:Client')
                ->findAll()
        );
        $this->assertCount(
            3,
            $logicalTest->getManager()
                ->getRepository('ChapleanUnitBundle:Status')
                ->findAll()
        );

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
        $logicalTest->loadPartialFixtures(
            array(
                'Chaplean\Bundle\UnitBundle\DataFixtures\Liip\LoadProviderData'
            )
        );

        $this->assertCount(
            1,
            $logicalTest->getManager()
                ->getRepository('ChapleanUnitBundle:Provider')
                ->findAll()
        );

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

        $this->assertCount(
            1,
            $logicalTest->getManager()
                ->getRepository('ChapleanUnitBundle:Client')
                ->findAll()
        );
        $this->assertCount(
            3,
            $logicalTest->getManager()
                ->getRepository('ChapleanUnitBundle:Status')
                ->findAll()
        );

        $logicalTest->tearDown();
    }

    /**
     * @return void
     */
    public function testTransactionIsActive()
    {
        $logicalTest = new LogicalTest();

        $logicalTest->setNamespaceFixtures('Chaplean\Bundle\UnitBundle\\');
        $logicalTest->setUpBeforeClass();
        $logicalTest->setUp();

        $this->assertEquals(
            1,
            $logicalTest->getManager()
                ->getConnection()
                ->getTransactionNestingLevel()
        );

        $logicalTest->tearDown();

        $this->assertEquals(
            0,
            $logicalTest->getManager()
                ->getConnection()
                ->getTransactionNestingLevel()
        );
    }

    /**
     * @return void
     */
    public function testLoadDataWithSetUpBeforeClass()
    {
        $logicalTest = new LogicalTest();

        $logicalTest->setNamespaceFixtures('Chaplean\Bundle\UnitBundle\\');
        $logicalTest->loadStaticFixtures(array('Chaplean\Bundle\UnitBundle\DataFixtures\Liip\LoadProviderData'));
        $logicalTest->setUpBeforeClass();
        $logicalTest->setUp();

        $this->assertCount(
            1,
            $logicalTest->getManager()
                ->getRepository('ChapleanUnitBundle:Provider')
                ->findAll()
        );

        $logicalTest->tearDown();
    }

    /**
     * @return void
     */
    public function testGetReference()
    {
        $logicalTest = new LogicalTest();
        $logicalTest->setNamespaceFixtures('Chaplean\Bundle\UnitBundle\\');
        $logicalTest->setUpBeforeClass();
        $logicalTest->setUp();

        $this->assertInstanceOf(Client::class, $logicalTest->getReference('client-1'));

        $logicalTest->tearDown();
    }

    /**
     * @return void
     */
    public function testTearDownAfterClass()
    {
        $logicalTest = new LogicalTest();
        $logicalTest->setNamespaceFixtures('Chaplean\Bundle\UnitBundle\\');

        $this->assertTrue($logicalTest->isOverrideNamespace());

        $logicalTest->tearDownAfterClass();

        $this->assertFalse($logicalTest->isOverrideNamespace());
    }
}
