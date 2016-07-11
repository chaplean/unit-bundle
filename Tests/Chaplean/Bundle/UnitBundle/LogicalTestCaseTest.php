<?php

namespace Tests\Chaplean\Bundle\UnitBundle;

use Chaplean\Bundle\UnitBundle\Entity\Client;
use Chaplean\Bundle\UnitBundle\Test\LogicalTestCase;
use Chaplean\Bundle\UnitBundle\Utility\FixtureUtility;
use Doctrine\ORM\EntityManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * LogicalTestCaseTest.php.
 *
 * @author                 Valentin - Chaplean <valentin@chaplean.com>
 * @copyright              2014 - 2016 Chaplean (http://www.chaplean.com)
 * @since                  3.0.0
 *
 * @backupStaticAttributes disabled
 */
class LogicalTestCaseTest extends WebTestCase
{
    /**
     * @return void
     */
    public function testConstructorLogicalTest()
    {
        $logicalTest = new LogicalTestCase();

        $this->assertInstanceOf(ContainerInterface::class, $logicalTest->getContainer());
        $this->assertInstanceOf(FixtureUtility::class, $logicalTest->getFixtureUtility());
        $this->assertInstanceOf(EntityManager::class, $logicalTest->getManager());
    }

    /**
     * @return void
     */
    public function testGetDefaultFixturesNamespaceWithoutParameter()
    {
        $logicalTest = new LogicalTestCase();

        $this->assertEquals('Chaplean\Bundle\UnitBundle\\', $logicalTest->getDefaultFixturesNamespace());
    }

    /**
     * @return void
     */
    public function testGetDefaultFixturesNamespaceWithParameter()
    {
        $containerMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
            ->getMock();

        $containerMock->expects($this->once())
            ->method('getParameter')
            ->with($this->equalTo('data_fixtures_namespace'))
            ->will($this->returnValue('Test\\'));

        $oldContainer = $this->getContainer();

        $logicalTest = new LogicalTestCase();
        $logicalTest->setContainer($containerMock);

        $this->assertEquals('Test\\', $logicalTest->getDefaultFixturesNamespace());

        $logicalTest->setContainer($oldContainer);
    }

    /**
     * @expectedException \Exception
     * @return void
     */
    public function testGetUndefinedProperty()
    {
        $logicalTest = new LogicalTestCase();

        /** @noinspection PhpUndefinedFieldInspection */
        $logicalTest->notDefined;
    }

    /**
     * @return void
     */
    public function testSetUpBeforeClassInitializeManager()
    {
        $logicalTest = new LogicalTestCase();
        $logicalTest->setNamespaceFixtures('Chaplean\Bundle\UnitBundle\\');
        $logicalTest->setUpBeforeClass();

        $this->assertInstanceOf(EntityManager::class, $logicalTest->getManager());
    }

    /**
     * @return void
     */
    public function testLoadDefaultFixtures()
    {
        $logicalTest = new LogicalTestCase();
        $logicalTest->setNamespaceFixtures('Chaplean\Bundle\UnitBundle\\');
        $logicalTest->setUpBeforeClass();
        $logicalTest->setUp();

        $clients = $logicalTest->getManager()
            ->getRepository('ChapleanUnitBundle:Client')
            ->findAll();

        $statuses = $logicalTest->getManager()
            ->getRepository('ChapleanUnitBundle:Status')
            ->findAll();

        $this->assertCount(1, $clients);
        $this->assertCount(3, $statuses);

        $logicalTest->tearDown();
        $logicalTest->tearDownAfterClass();
    }

    /**
     * @return void
     */
    public function testLoadPartialFixturesWithoutManager()
    {
        $logicalTest = new LogicalTestCase();
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
        $logicalTest->tearDownAfterClass();
    }

    /**
     * @return void
     */
    public function testLoadFixturesByContext()
    {
        $logicalTest = new LogicalTestCase();
        $logicalTest->setUp();

        $logicalTest->setNamespaceFixtures('Chaplean\Bundle\UnitBundle\\');
        $logicalTest->loadFixturesByContext('DefaultData');

        $clients = $logicalTest->getManager()
            ->getRepository('ChapleanUnitBundle:Client')
            ->findAll();

        $statuses = $logicalTest->getManager()
            ->getRepository('ChapleanUnitBundle:Status')
            ->findAll();

        $this->assertCount(1, $clients);
        $this->assertCount(3, $statuses);

        $logicalTest->tearDown();
        $logicalTest->tearDownAfterClass();
    }

    /**
     * @return void
     */
    public function testLoadFixturesByContextWithoutSettingNamespace()
    {
        FixtureUtility::getInstance()
            ->setNamespace('');

        $this->assertEmpty(
            FixtureUtility::getInstance()
                ->getNamespace()
        );

        $logicalTest = new LogicalTestCase();
        $logicalTest->loadFixturesByContext('MultiCompanies');

        $this->assertNotEmpty($logicalTest->getNamespace());
    }

    /**
     * @return void
     */
    public function testTransactionIsActive()
    {
        $logicalTest = new LogicalTestCase();

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

        $logicalTest->tearDown();
        $logicalTest->tearDownAfterClass();
    }

    /**
     * @return void
     */
    public function testLoadDataWithSetUpBeforeClass()
    {
        $logicalTest = new LogicalTestCase();

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
        $logicalTest->tearDownAfterClass();
    }

    /**
     * @return void
     */
    public function testGetReference()
    {
        $logicalTest = new LogicalTestCase();
        $logicalTest->setNamespaceFixtures('Chaplean\Bundle\UnitBundle\\');
        $logicalTest->setUpBeforeClass();
        $logicalTest->setUp();

        $this->assertInstanceOf(Client::class, $logicalTest->getReference('client-1'));

        $logicalTest->tearDown();
        $logicalTest->tearDownAfterClass();
    }

    /**
     * @return void
     */
    public function testResetStaticProperties()
    {
        $logicalTest = new LogicalTestCase();

        $logicalTest->setNamespaceFixtures('Chaplean\Bundle\UnitBundle\\');
        $logicalTest->setUpBeforeClass();
        $logicalTest->setUp();

        $logicalTest->resetStaticProperties();

        $this->assertNull($logicalTest->getContainer());
        $this->assertNull($logicalTest->getFixtureUtility());
        $this->assertNull($logicalTest->getManager());

        $logicalTest->tearDown();
        $logicalTest->tearDownAfterClass();
    }

    /**
     * @return void
     */
    public function testTearDownAfterClass()
    {
        $logicalTest = new LogicalTestCase();
        $logicalTest->setNamespaceFixtures('Chaplean\Bundle\TestBundle\\');

        $this->assertEquals('Chaplean\Bundle\TestBundle\\', $logicalTest->getNamespace());

        $logicalTest->tearDownAfterClass();

        $this->assertEquals('Chaplean\Bundle\UnitBundle\\', $logicalTest->getNamespace());
    }
}
