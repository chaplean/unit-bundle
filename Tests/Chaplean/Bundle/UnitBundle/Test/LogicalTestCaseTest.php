<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Test;

use Chaplean\Bundle\UnitBundle\Entity\Client;
use Chaplean\Bundle\UnitBundle\Test\LogicalTestCase;
use Chaplean\Bundle\UnitBundle\Utility\FixtureUtility;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Tests\Fixtures\Foo1Bar2Type;
use Tests\Chaplean\Bundle\UnitBundle\Form\Type\FormWithoutCrsfTokenType;

/**
 * LogicalTestCaseTest.php.
 *
 * @author                 Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright              2014 - 2016 Chaplean (http://www.chaplean.coop)
 * @since                  3.0.0
 */
class LogicalTestCaseTest extends LogicalTestCase
{
    /** @var ContainerInterface */
    private $oldContainer = null;

    public static function setUpBeforeClass()
    {
        self::markTestSkipped('');
        parent::setUpBeforeClass();
    }

    /**
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->oldContainer = LogicalTestCase::$container;
    }

    /**
     * @return void
     */
    public function tearDown()
    {
        LogicalTestCase::$container = $this->oldContainer;

        parent::tearDown();
    }

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
     * @runInSeparateProcess
     */
    public function testGetDefaultFixturesNamespaceWithParameter()
    {
        $containerMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
            ->getMock();

        $containerMock->expects($this->once())
            ->method('getParameter')
            ->with($this->equalTo('data_fixtures_namespace'))
            ->will($this->returnValue('Test\\'));

        $logicalTest = new LogicalTestCase();
        $logicalTest->setContainer($containerMock);

        $this->assertEquals('Test\\', $logicalTest->getDefaultFixturesNamespace());
    }

    /**
     * @return void
     * @runInSeparateProcess
     */
    public function testDatafixturesDisabled()
    {
        $this->markTestSkipped('mock get(\'doctrine\') is not propagate in FixtureUtility:153 !');
        $containerMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
            ->getMock();

        $logicalTest = new LogicalTestCase();

        $containerMock
            ->expects($this->any())
            ->method('get')
            ->withConsecutive(
                array($this->equalTo('doctrine')),
                array($this->equalTo('chaplean_unit.swiftmailer_cache'))
            )
            ->willReturnOnConsecutiveCalls(
                $logicalTest->getContainer()->get('doctrine'),
                $logicalTest->getContainer()->get('chaplean_unit.swiftmailer_cache')
            );

        $containerMock
            ->expects($this->once())
            ->method('getParameter')
            ->with($this->equalTo('data_fixtures_namespace'))
            ->will($this->returnValue(false));

        $logicalTest->setContainer($containerMock);

        $this->assertEquals(false, $logicalTest->getDefaultFixturesNamespace());

        $logicalTest::setUpBeforeClass();
    }

    /**
     * @return void
     * @runInSeparateProcess
     * @expectedException \Exception
     * @expectedExceptionMessage Datafixture is disabled, check 'data_fixtures_namespace' value
     */
    public function testDatafixturesDisabledAndLoadByContext()
    {
        $containerMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
            ->getMock();

        $logicalTest = new LogicalTestCase();

        $containerMock
            ->expects($this->once())
            ->method('getParameter')
            ->with($this->equalTo('data_fixtures_namespace'))
            ->will($this->returnValue(false));

        $logicalTest->setContainer($containerMock);
        $this->assertEquals(false, $logicalTest->getDefaultFixturesNamespace());

        $logicalTest::loadFixturesByContext('Foo');
    }

    /**
     * @return void
     * @runInSeparateProcess
     * @expectedException \Exception
     * @expectedExceptionMessage Datafixture is disabled, check 'data_fixtures_namespace' value
     */
    public function testDatafixturesDisabledAndLoadPartialByContext()
    {
        $this->markTestSkipped('mock get(\'doctrine\') is not propagate in FixtureUtility:153 !');
        $containerMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
            ->getMock();

        $logicalTest = new LogicalTestCase();

        $containerMock
            ->expects($this->exactly(1))
            ->method('get')
            ->withConsecutive(
                array($this->equalTo('doctrine')),
                array($this->equalTo('chaplean_unit.swiftmailer_cache'))
            )
            ->willReturnOnConsecutiveCalls(
                $logicalTest->getContainer()->get('doctrine'),
                $logicalTest->getContainer()->get('chaplean_unit.swiftmailer_cache')
            );

        $containerMock
            ->expects($this->once())
            ->method('getParameter')
            ->with($this->equalTo('data_fixtures_namespace'))
            ->will($this->returnValue(false));

        $logicalTest->setContainer($containerMock);
        $this->assertEquals(false, $logicalTest->getDefaultFixturesNamespace());

        $logicalTest::setUpBeforeClass();
        $logicalTest->setUp();

        $logicalTest->loadPartialFixturesByContext('Bar');
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
    public function testConstructInitializeManager()
    {
        $logicalTest = new LogicalTestCase();

        $this->assertInstanceOf(EntityManager::class, $logicalTest->getManager());
    }

    /**
     * @return void
     */
    public function testRepositoryManagerEqualsToMine()
    {
        $logicalTest = new LogicalTestCase();
        $logicalTest::setUpBeforeClass();
        $logicalTest->setUp();

        $repository = $logicalTest->em->getRepository('ChapleanUnitBundle:Client');

        $this->assertEquals($logicalTest->em, $repository->getManager());

        $logicalTest->tearDown();
        $logicalTest::tearDownAfterClass();
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
        $logicalTest::tearDownAfterClass();
    }

    /**
     * @return void
     */
    public function testLoadPartialFixturesWithoutManager()
    {
        $logicalTest = new LogicalTestCase();
        $logicalTest::setUpBeforeClass();
        $logicalTest->setUp();

        $logicalTest->setNamespaceFixtures('Chaplean\Bundle\UnitBundle\\');
        $logicalTest->loadPartialFixtures(
            array(
                'Chaplean\Bundle\UnitBundle\DataFixtures\Liip\LoadPartial\LoadProviderData'
            )
        );

        $this->assertCount(
            1,
            $logicalTest->getManager()
                ->getRepository('ChapleanUnitBundle:Provider')
                ->findAll()
        );

        $logicalTest->tearDown();
        $logicalTest::tearDownAfterClass();
    }

    /**
     * @return void
     */
    public function testLoadFixturesByContext()
    {
        $logicalTest = new LogicalTestCase();
        $logicalTest::setUpBeforeClass();
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
        $logicalTest::tearDownAfterClass();
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
        $logicalTest::setUpBeforeClass();
        $logicalTest->setUp();

        $logicalTest->loadFixturesByContext('MultiCompanies');

        $this->assertNotEmpty($logicalTest->getNamespace());

        $logicalTest->tearDown();
        $logicalTest::tearDownAfterClass();
    }

    /**
     * @return void
     */
    public function testTransactionIsActive()
    {
        $logicalTest = new LogicalTestCase();

        $logicalTest->setNamespaceFixtures('Chaplean\Bundle\UnitBundle\\');
        $logicalTest::setUpBeforeClass();
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
        $logicalTest::tearDownAfterClass();
    }

    /**
     * @return void
     */
    public function testLoadDataWithSetUpBeforeClass()
    {
        $logicalTest = new LogicalTestCase();

        $logicalTest->setNamespaceFixtures('Chaplean\Bundle\UnitBundle\\');
        $logicalTest->loadStaticFixtures(array('Chaplean\Bundle\UnitBundle\DataFixtures\Liip\LoadProviderData'));
        $logicalTest::setUpBeforeClass();
        $logicalTest->setUp();

        $this->assertCount(
            1,
            $logicalTest->getManager()
                ->getRepository('ChapleanUnitBundle:Provider')
                ->findAll()
        );

        $logicalTest->tearDown();
        $logicalTest::tearDownAfterClass();
    }

    /**
     * @return void
     */
    public function testGetReference()
    {
        $logicalTest = new LogicalTestCase();
        $logicalTest->setNamespaceFixtures('Chaplean\Bundle\UnitBundle\\');
        $logicalTest::setUpBeforeClass();
        $logicalTest->setUp();

        $this->assertInstanceOf(Client::class, $logicalTest->getReference('client-1'));

        $logicalTest->tearDown();
        $logicalTest->tearDownAfterClass();
    }

    /**
     * @return void
     */
    public function testTearDownAfterClass()
    {
        $logicalTest = new LogicalTestCase();
        $logicalTest::setUpBeforeClass();
        $logicalTest->setUp();

        $logicalTest->setNamespaceFixtures('Chaplean\Bundle\TestBundle\\');

        $this->assertEquals('Chaplean\Bundle\TestBundle\\', $logicalTest->getNamespace());

        $logicalTest->tearDown();
        $logicalTest::tearDownAfterClass();

        $this->assertEquals('Chaplean\Bundle\UnitBundle\\', $logicalTest->getNamespace());
    }

    /**
     * @return void
     */
    public function testGetProtectedMethod()
    {
        $logicalTest = new LogicalTestCase();
        $logicalTest::setUpBeforeClass();
        $logicalTest->setUp();

        $method = $logicalTest->getNotPublicMethod(DummyClassWithNotPublicMethod::class, 'getFoo');

        $this->assertInstanceOf(\ReflectionMethod::class, $method);
        $this->assertEquals('foo', $method->invoke(new DummyClassWithNotPublicMethod()));

        $logicalTest->tearDown();
        $logicalTest::tearDownAfterClass();
    }

    /**
     * @return void
     */
    public function testGetPrivateMethod()
    {
        $logicalTest = new LogicalTestCase();
        $logicalTest::setUpBeforeClass();
        $logicalTest->setUp();

        $method = $logicalTest->getNotPublicMethod(DummyClassWithNotPublicMethod::class, 'getBar');

        $this->assertInstanceOf(\ReflectionMethod::class, $method);
        $this->assertEquals('bar', $method->invoke(new DummyClassWithNotPublicMethod()));

        $logicalTest->tearDown();
        $logicalTest::tearDownAfterClass();
    }

    /**
     * @return void
     */
    public function testGetProtectedMethodWithArgument()
    {
        $logicalTest = new LogicalTestCase();
        $method = $logicalTest->getNotPublicMethod(DummyClassWithNotPublicMethod::class, 'getWithArg');

        $this->assertInstanceOf(\ReflectionMethod::class, $method);
        $this->assertEquals('arg', $method->invokeArgs(new DummyClassWithNotPublicMethod(), array('arg')));
    }

    /**
     * @return void
     */
    public function testCreateSymfonyClientWithSameEntityManager()
    {
        $logicalTest = new LogicalTestCase();
        $logicalTest::setUpBeforeClass();
        $logicalTest->setUp();

        $createClientMethod = $logicalTest->getNotPublicMethod(LogicalTestCase::class, 'createClient');
        /** @var \Symfony\Bundle\FrameworkBundle\Client $client */
        $client = $createClientMethod->invoke($logicalTest);

        $this->assertEquals(spl_object_hash($logicalTest->em), spl_object_hash($client->getContainer()->get('doctrine')->getManager()));
        $this->assertTrue($logicalTest->em->getConnection()->isTransactionActive());
        $this->assertTrue($client->getContainer()->get('doctrine')->getConnection()->isTransactionActive());

        $logicalTest->tearDown();
        $logicalTest::tearDownAfterClass();
    }

    /**
     * @return void
     */
    public function testDefaultNamespace()
    {
        $logicalTest = new LogicalTestCase();
        $logicalTest::setUpBeforeClass();
        $logicalTest->setUp();

        $this->assertEquals('Chaplean\\Bundle\\UnitBundle\\', $logicalTest->getNamespace());

        $logicalTest->tearDown();
        $logicalTest::tearDownAfterClass();
    }

    /**
     * @return void
     */
    public function testSetNamespace()
    {
        $logicalTest = new LogicalTestCase();
        $logicalTest::setUpBeforeClass();
        $logicalTest->setUp();

        $logicalTest::setNamespaceFixtures('App\\Bundle\\RestBundle\\');
        $this->assertEquals('App\\Bundle\\RestBundle\\', $logicalTest->getNamespace());

        $logicalTest->tearDown();
        $logicalTest::tearDownAfterClass();
    }

    /**
     * @return void
     */
    public function testSetAndResetNamespace()
    {
        $logicalTest = new LogicalTestCase();
        $logicalTest::setUpBeforeClass();
        $logicalTest->setUp();

        $logicalTest::setNamespaceFixtures('App\\Bundle\\RestBundle\\');
        $this->assertEquals('App\\Bundle\\RestBundle\\', $logicalTest->getNamespace());

        $logicalTest::resetDefaultNamespaceFixtures();

        $this->assertEquals('Chaplean\\Bundle\\UnitBundle\\', $logicalTest->getNamespace());

        $logicalTest->tearDown();
        $logicalTest::tearDownAfterClass();
    }

    /**
     * @return void
     */
    public function testTwoSetupWithoutTransactionalIssue()
    {
        $logicalTest = new LogicalTestCase();
        $logicalTest::setUpBeforeClass();
        $logicalTest->setUp();
        $logicalTest->setUp();

        $logicalTest->assertEquals(1, $logicalTest->em->getConnection()->getTransactionNestingLevel());

        $logicalTest->tearDown();
        $logicalTest::tearDownAfterClass();
    }

    /**
     * @return void
     */
    public function testGetCrsfToken()
    {
        $logicalTest = new LogicalTestCase();
        $logicalTest::setUpBeforeClass();
        $logicalTest->setUp();

        $this->assertNotEmpty($logicalTest->getCrsfToken(Foo1Bar2Type::class));

        $logicalTest->tearDown();
        $logicalTest::tearDownAfterClass();
    }

    /**
     * @return void
     * @expectedException \Exception
     */
    public function testGetCrsfTokenWithDisabledCrsfToken()
    {
        $logicalTest = new LogicalTestCase();
        $logicalTest::setUpBeforeClass();
        $logicalTest->setUp();

        $logicalTest->getCrsfToken(FormWithoutCrsfTokenType::class);

        $logicalTest->tearDown();
        $logicalTest::tearDownAfterClass();
    }

    /**
     * @return void
     * @expectedException \LogicException
     * @expectedExceptionMessage You must define test_roles in your parameters_test.yml to use this function.
     */
    public function testRolesProviderWithoutConfiguration()
    {
        $logicalTest = new LogicalTestCase();

        $logicalTest->rolesProvider(array());
    }

    /**
     * @return void
     * @runInSeparateProcess
     */
    public function testRolesProviderWithConfiguration()
    {
        $containerMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
            ->getMock();

        $containerMock->expects($this->once())
            ->method('getParameter')
            ->with($this->equalTo('test_roles'))
            ->will($this->returnValue(array('NotLogged' => '', 'User' => 'user-1')));

        LogicalTestCase::$container = $containerMock;
        $logicalTest = new LogicalTestCase();

        $expected = array(
            'NotLogged' => array('', 'one param'),
            'User'      => array('user-1', 'two', 'param')
        );
        $actual = $logicalTest->rolesProvider(array(
            'NotLogged' => 'one param',
            'User'      => array('two', 'param')
        ));
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return void
     * @runInSeparateProcess
     * @expectedException \LogicException
     */
    public function testRolesProviderWithBadParameter()
    {
        $containerMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
            ->getMock();

        $containerMock->expects($this->once())
            ->method('getParameter')
            ->with($this->equalTo('test_roles'))
            ->will($this->returnValue(array('NotLogged' => '', 'User' => 'user-1')));

        LogicalTestCase::$container = $containerMock;
        $logicalTest = new LogicalTestCase();

        $logicalTest->rolesProvider(array(
              'NotLogged'      => 'one param',
              'UnexpectedRole' => array('two', 'param')
        ));
    }

    /**
     * @return void
     * @runInSeparateProcess
     * @expectedException \LogicException
     */
    public function testRolesProviderWithMissingParameter()
    {
        $containerMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
            ->getMock();

        $containerMock->expects($this->once())
            ->method('getParameter')
            ->with($this->equalTo('test_roles'))
            ->will($this->returnValue(array('NotLogged' => '', 'User' => 'user-1')));

        LogicalTestCase::$container = $containerMock;
        $logicalTest = new LogicalTestCase();

        $logicalTest->rolesProvider(array(
            'NotLogged'      => 'one param',
        ));
    }

    /**
     * @return void
     * @runInSeparateProcess
     */
    public function testCreateClientWithRole()
    {
        $containerMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
            ->getMock();

        $containerMock->expects($this->once())
            ->method('getParameter')
            ->with($this->equalTo('test_roles'))
            ->will($this->returnValue(array('NotLogged' => '', 'User' => 'user-1')));

        LogicalTestCase::$container = $containerMock;
        $logicalTest = new LogicalTestCase();

        $logicalTest->rolesProvider(
            array(
                'NotLogged' => 'one param',
                'User'      => array('two', 'param')
            )
        );

        $client = $logicalTest->createClientWith('');
        $this->assertInstanceOf(\Symfony\Bundle\FrameworkBundle\Client::class, $client);
        $this->assertNull($client->getContainer()->get('security.token_storage')->getToken());
    }
}

/**
 * Class DummyClassWithNotPublicMethod.
 *
 * @package   Tests\Chaplean\Bundle\UnitBundle\Test
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.coop)
 * @since     4.0.0
 */
class DummyClassWithNotPublicMethod
{
    /**
     * @return string
     */
    protected function getFoo()
    {
        return 'foo';
    }

    /**
     * @return string
     */
    private function getBar()
    {
        return 'bar';
    }

    /**
     * @param string $string
     *
     * @return string
     */
    protected function getWithArg($string)
    {
        return $string;
    }
}
