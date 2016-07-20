<?php

namespace Chaplean\Bundle\UnitBundle\Test;

use Chaplean\Bundle\UnitBundle\Utility\FixtureUtility;
use Chaplean\Bundle\UnitBundle\Utility\NamespaceUtility;
use Chaplean\Bundle\UnitBundle\Utility\RestClient;
use Chaplean\Bundle\UnitBundle\Utility\SwiftMailerCacheUtility;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * LogicalTestCase.
 *
 * @author    Matthias - Chaplean <matthias@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     4.0.0
 *
 * @property EntityManager em Entity Manager.
 */
class LogicalTestCase extends WebTestCase
{
    /**
     * @var ContainerInterface
     */
    protected static $container;

    /**
     * @var ReferenceRepository
     */
    public static $fixtures;

    /**
     * @var FixtureUtility
     */
    private static $fixtureUtility = null;

    /**
     * @var boolean
     */
    protected static $databaseLoaded = false;

    /**
     * @var string
     */
    protected static $hashFixtures;

    /**
     * @var Registry
     */
    private static $doctrineRegistry = null;

    /**
     * @var SwiftMailerCacheUtility
     */
    private static $swiftmailerCacheUtility = null;

    /**
     * @var array
     */
    private static $userFixtures = array();

    /**
     * @var boolean
     */
    protected static $withDefaultData = true;

    /**
     * @var boolean
     */
    protected static $datafixturesEnabled = true;

    /**
     * Construct
     *
     * @param string|null $name
     * @param array       $data
     * @param string      $dataName
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        if (self::$fixtureUtility === null) {
            self::$fixtureUtility = FixtureUtility::getInstance();
        }

        if (self::$container === null) {
            $this->setContainer(parent::getContainer());
        }

        if (self::$doctrineRegistry === null) {
            self::$doctrineRegistry = $this->getContainer()
                ->get('doctrine');
        }

        $namespaceFixtures = $this->getFixtureUtility()
            ->getNamespace();

        if (empty($namespaceFixtures)) {
            self::resetDefaultNamespaceFixtures();
        }
    }

    /**
     * @param string $name Property name.
     *
     * @return \Doctrine\ORM\EntityManager
     * @throws \Exception
     */
    public function __get($name)
    {
        if ($name == 'em') {
            return $this->getManager();
        }

        throw new \Exception('Undefined property ' . $name);
    }

    /**
     * @param string|object $user
     * @param Client        $client
     *
     * @return void
     */
    public function authenticate($user, Client $client = null)
    {
        $usernameTokenPassword = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->getContainer()
            ->get('security.token_storage')
            ->setToken($usernameTokenPassword);

        if ($client !== null) {
            $client->getContainer()
                ->get('security.token_storage')
                ->setToken($usernameTokenPassword);

            /** @var Session $session */
            $session = $client->getContainer()
                ->get('session');

            $session->set('_security_main', serialize($usernameTokenPassword));
            $session->save();

            $cookie = new Cookie($session->getName(), $session->getId());
            $client->getCookieJar()
                ->set($cookie);
        }
    }

    /**
     * @return void
     */
    public function cleanMailDir()
    {
        if (self::$swiftmailerCacheUtility === null) {
            return;
        }

        self::$swiftmailerCacheUtility->cleanMailDir();
    }

    /**
     * @return RestClient
     */
    public function createRestClient()
    {
        return new RestClient($this->getContainer());
    }

    /**
     * Creates a Client.
     *
     * @param array $options An array of options to pass to the createKernel class
     * @param array $server  An array of server parameters
     *
     * @return Client A Client instance
     */
    protected static function createClient(array $options = array(), array $server = array())
    {
        $client = parent::createClient($options, $server);
        $client->getContainer()
            ->set('doctrine', self::$doctrineRegistry);

        return $client;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return self::$container;
    }

    /**
     * @return string
     */
    public static function getDefaultFixturesNamespace()
    {
        try {
            $dataFixturesNamespaceParameter = self::$container->getParameter('data_fixtures_namespace');

            if (is_bool($dataFixturesNamespaceParameter) && !$dataFixturesNamespaceParameter) {
                self::$datafixturesEnabled = false;
            }

            return $dataFixturesNamespaceParameter;
        } catch (InvalidArgumentException $e) {
            return 'App\Bundle\RestBundle\\';
        }
    }

    /**
     * Get doctrineRegistry.
     *
     * @return Registry
     */
    public static function getDoctrineRegistry()
    {
        return self::$doctrineRegistry;
    }

    /**
     * @param string $reference
     *
     * @deprecated use getReference() instead
     * @return null|object
     */
    public function getEntity($reference)
    {
        return $this->getReference($reference);
    }

    /**
     * @return FixtureUtility
     */
    public function getFixtureUtility()
    {
        return self::$fixtureUtility;
    }

    /**
     * @return EntityManager
     */
    public function getManager()
    {
        return self::$doctrineRegistry->getManager();
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->getFixtureUtility()
            ->getNamespace();
    }

    /**
     * @param string $className
     * @param string $methodName
     *
     * @return \ReflectionMethod
     */
    public function getNotPublicMethod($className, $methodName)
    {
        $class = new \ReflectionClass($className);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * @param string $reference
     *
     * @return object
     */
    public function getReference($reference)
    {
        if (self::$fixtures === null) {
            return null;
        }

        return self::$fixtures->getReference($reference);
    }

    /**
     * @param string  $context
     * @param boolean $withDefaultData
     *
     * @return void
     * @throws \Exception
     */
    public static function loadFixturesByContext($context, $withDefaultData = false)
    {
        if (self::$fixtureUtility === null) {
            throw new \Exception('FixtureUtility needs to be instanciated');
        } elseif (!self::$datafixturesEnabled) {
            throw new \Exception('Datafixture is disabled, check \'data_fixtures_namespace\' value');
        }

        $contextFixtures = NamespaceUtility::getClassNamesByContext(self::$fixtureUtility->getNamespace(), $context);

        self::loadStaticFixtures($contextFixtures, $withDefaultData);
    }

    /**
     * @param array $classNames List of fully qualified class names of fixtures to load.
     *
     * @return void
     */
    public function loadPartialFixtures(array $classNames)
    {
        self::$fixtures = $this->getFixtureUtility()
            ->loadPartialFixtures($classNames, $this->getManager())->getReferenceRepository();
    }

    /**
     * @param string $context
     *
     * @return void
     * @throws \Exception
     */
    public function loadPartialFixturesByContext($context)
    {
        if (!self::$datafixturesEnabled) {
            throw new \Exception('Datafixture is disabled, check \'data_fixtures_namespace\' value');
        }

        $fixtureUtility = $this->getFixtureUtility();
        $classNames = NamespaceUtility::getClassNamesByContext($fixtureUtility->getNamespace(), $context);

        self::$fixtures = $fixtureUtility->loadPartialFixtures($classNames, $this->getManager())->getReferenceRepository();
    }

    /**
     * @param array $classNames
     *
     * @return void
     * @throws \Exception
     */
    private static function loadFixturesOnSetUp(array $classNames)
    {
        if (self::$fixtureUtility === null) {
            throw new \Exception('FixtureUtility needs to be instanciated');
        }

        $hashFixtures = md5(serialize($classNames));

        if ($hashFixtures !== self::$hashFixtures) {
            self::$hashFixtures = $hashFixtures;

            self::$fixtures = self::$fixtureUtility->loadFixtures($classNames)->getReferenceRepository();
        }

        self::$databaseLoaded = true;
    }

    /**
     * @param array   $fixtures
     * @param boolean $withDefaultData
     *
     * @return void
     */
    public static function loadStaticFixtures(array $fixtures = array(), $withDefaultData = false)
    {
        self::$userFixtures = $fixtures;
        self::$withDefaultData = $withDefaultData;
    }

    /**
     * @return array
     * @throws \Exception Exception.
     */
    public function readMessages()
    {
        return self::$swiftmailerCacheUtility->readMessages();
    }

    /**
     * @return void
     * @throws \Exception
     */
    public static function resetDefaultNamespaceFixtures()
    {
        if (self::$fixtureUtility === null) {
            throw new \Exception('FixtureUtility needs to be instanciated');
        }

        self::$fixtureUtility->setNamespace(self::getDefaultFixturesNamespace());
    }

    /**
     * @return void
     */
    public static function resetManagerIfNecessary()
    {
        $manager = self::$doctrineRegistry->getManager();

        if ($manager !== null && !$manager->isOpen()) {
            self::$doctrineRegistry->resetManager();
        }
    }

    /**
     * @param ContainerInterface $container
     *
     * @return void
     */
    public function setContainer(ContainerInterface $container)
    {
        self::$container = $container;

        if ($this->getFixtureUtility() !== null) {
            $this->getFixtureUtility()
                ->setContainer($container);
        }
    }

    /**
     * @param string $namespace Namespace parent of folder DataFixtures (example: 'App\\Bundle\\FrontBundle\\').
     *
     * @return void
     * @throws \Exception
     */
    public static function setNamespaceFixtures($namespace)
    {
        if (self::$fixtureUtility === null) {
            throw new \Exception('FixtureUtility needs to be instanciated');
        }

        self::$fixtureUtility->setNamespace($namespace);
    }

    /**
     * Start transaction
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        self::resetManagerIfNecessary();

        $manager = $this->getManager();
        $manager->getUnitOfWork()->clear();
        $nbTransactions = $manager->getConnection()->getTransactionNestingLevel();

        if (self::$databaseLoaded && $nbTransactions < 1) {
            $manager->beginTransaction();
        }

        $this->cleanMailDir();
    }

    /**
     * Load empty data fixture to generate the database schema even if no data are given
     *
     * @return void
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$doctrineRegistry = self::$container->get('doctrine');
        self::$swiftmailerCacheUtility = self::$container->get('chaplean_unit.swiftmailer_cache');
        self::$doctrineRegistry->getManager()->getUnitOfWork()->clear();

        $dataFixturesToLoad = self::$userFixtures;

        if (self::$withDefaultData && self::$datafixturesEnabled) {
            $dataFixturesToLoad = array_merge(self::$fixtureUtility->loadDefaultFixtures(), $dataFixturesToLoad);
        }

        if (self::$datafixturesEnabled) {
            self::loadFixturesOnSetUp($dataFixturesToLoad);
        }
    }

    /**
     * Close connection to avoid "Too Many Connection" error and rollback transaction
     *
     * @return void
     */
    public function tearDown()
    {
        if ($this->getManager() !== null) {
            $connection = $this->getManager()
                ->getConnection();

            if ($connection->getTransactionNestingLevel() == 1 && self::$databaseLoaded) {
                $this->getManager()
                    ->rollback();
            }

            $connection->close();
        }

        // Unauthenticate user between each test
        if ($this->getContainer() !== null) {
            $this->getContainer()
                ->get('security.token_storage')
                ->setToken(null);
        }

        parent::tearDown();
    }

    /**
     * @return void
     */
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        self::resetDefaultNamespaceFixtures();

        self::$userFixtures = array();
        self::$withDefaultData = true;
    }
}
