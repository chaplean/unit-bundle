<?php

namespace Chaplean\Bundle\UnitBundle\Test;

use Chaplean\Bundle\UnitBundle\Utility\FixtureUtility;
use Chaplean\Bundle\UnitBundle\Utility\NamespaceUtility;
use Chaplean\Bundle\UnitBundle\Utility\RestClient;
use Chaplean\Bundle\UnitBundle\Utility\SwiftMailerCacheUtility;
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
    protected static $fixtures;

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
     * @var EntityManager
     */
    private static $manager = null;

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
     * Construct
     *
     * @param string|null $name
     * @param array       $data
     * @param string      $dataName
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        if (self::$container === null) {
            $this->setContainer(parent::getContainer());
        }

        if (self::$manager === null) {
            self::$manager = self::$container->get('doctrine')
                ->getManager();
        }

        if (self::$fixtureUtility === null) {
            self::$fixtureUtility = FixtureUtility::getInstance();
            self::$fixtureUtility->setContainer(self::$container);
        }

        if (empty(self::$fixtureUtility->getNamespace())) {
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

            return $dataFixturesNamespaceParameter;
        } catch (InvalidArgumentException $e) {
            return 'App\Bundle\RestBundle\\';
        }
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
        return self::$manager;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return self::$fixtureUtility->getNamespace();
    }

    /**
     * @param string $reference
     *
     * @return object
     */
    public function getReference($reference)
    {
        return self::$fixtures->getReference($reference);
    }

    /**
     * @param string  $context
     * @param boolean $withDefaultData
     *
     * @return void
     */
    public static function loadFixturesByContext($context, $withDefaultData = false)
    {
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
        self::$fixtures = self::$fixtureUtility->loadPartialFixtures($classNames, self::$manager)
            ->getReferenceRepository();
    }

    /**
     * @param string $context
     *
     * @return void
     */
    public function loadPartialFixturesByContext($context)
    {
        $classNames = NamespaceUtility::getClassNamesByContext(self::$fixtureUtility->getNamespace(), $context);

        self::$fixtures = self::$fixtureUtility->loadPartialFixtures($classNames, self::$manager)
            ->getReferenceRepository();
    }

    /**
     * @param array $classNames
     *
     * @return void
     */
    private static function loadFixturesOnSetUp(array $classNames)
    {
        $hashFixtures = md5(serialize($classNames));

        if ($hashFixtures !== self::$hashFixtures) {
            self::$hashFixtures = $hashFixtures;
            self::$fixtures = self::$fixtureUtility->loadFixtures($classNames)
                ->getReferenceRepository();
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
     */
    public static function resetDefaultNamespaceFixtures()
    {
        if (self::$fixtureUtility !== null) {
            self::$fixtureUtility->setNamespace(self::getDefaultFixturesNamespace());
        }
    }

    /**
     * @return void
     */
    public static function resetStaticProperties()
    {
        self::$container = null;
        self::$fixtureUtility = null;
        self::$manager = null;
    }

    /**
     * @param ContainerInterface $container
     *
     * @return void
     */
    public function setContainer(ContainerInterface $container)
    {
        self::$container = $container;
    }

    /**
     * @param string $namespace Namespace parent of folder DataFixtures (example: 'App\\Bundle\\FrontBundle\\').
     *
     * @return void
     */
    public static function setNamespaceFixtures($namespace)
    {
        self::$fixtureUtility->setNamespace($namespace);
    }

    /**
     * Start transaction
     *
     * @return void
     */
    public function setUp()
    {
        // If there is any Exception before, EntityManager is closed so we need to reopen it
        if (!self::$manager->isOpen()) {
            self::$container = parent::getContainer();
            self::$manager = self::$container->get('doctrine')
                ->getManager();
        }

        if (self::$databaseLoaded) {
            self::$manager->beginTransaction();
        }

        $this->cleanMailDir();

        parent::setUp();
    }

    /**
     * Load empty data fixture to generate the database schema even if no data are given
     *
     * @return void
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$swiftmailerCacheUtility = self::$container->get('chaplean_unit.swiftmailer_cache');

        $dataFixturesToLoad = self::$userFixtures;

        if (self::$withDefaultData) {
            $dataFixturesToLoad = array_merge(self::$fixtureUtility->loadDefaultFixtures(), $dataFixturesToLoad);
        }

        self::loadFixturesOnSetUp($dataFixturesToLoad);
    }

    /**
     * Close connection to avoid "Too Many Connection" error and rollback transaction
     *
     * @return void
     */
    public function tearDown()
    {
        if (self::$manager !== null) {
            $connection = self::$manager->getConnection();

            if ($connection->getTransactionNestingLevel() > 0 && self::$databaseLoaded) {
                self::$manager->rollback();
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
