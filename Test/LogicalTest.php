<?php

namespace Chaplean\Bundle\UnitBundle\Test;

use Chaplean\Bundle\UnitBundle\Utility\ContainerUtility;
use Chaplean\Bundle\UnitBundle\Utility\FixtureUtility;
use Chaplean\Bundle\UnitBundle\Utility\FrontClient;
use Chaplean\Bundle\UnitBundle\Utility\NamespaceUtility;
use Chaplean\Bundle\UnitBundle\Utility\RestClient;
use Chaplean\Bundle\UnitBundle\Utility\SwiftMailerCacheUtility;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;

//Tests\\(.+)\\.+Test

/**
 * LogicalTest.
 *
 * @author    Matthias - Chaplean <matthias@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     0.1.0
 */
class LogicalTest extends WebTestCase
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var SwiftMailerCacheUtility
     */
    protected $swiftmailerCacheUtility;

    /**
     * @var ReferenceRepository
     */
    protected static $fixtures;
    
    /**
     * @var array
     */
    protected static $defaultFixtures;

    /**
     * @var boolean
     */
    protected static $iWantDefaultData;

    /**
     * @var boolean
     */
    protected static $databaseLoaded = false;

    /**
     * @var string
     */
    public static $hashFixtures;

    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();

        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->swiftmailerCacheUtility = $this->getContainer()->get('chaplean_unit.swiftmailer_cache');

        self::resetNamespaceFixtures();
        self::$iWantDefaultData = true;
    }

    /**
     * @param mixed  $user
     * @param Client $client
     *
     * @return void
     */
    public function authenticate($user, $client = null)
    {
        $usernameTokenPassword = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->getContainer()->get('security.token_storage')->setToken($usernameTokenPassword);

        if ($client instanceof Client) {
            $client->getContainer()->get('security.token_storage')->setToken($usernameTokenPassword);
            /** @var Session $session */
            $session = $client->getContainer()->get('session');
            $session->set('_security_main', serialize($usernameTokenPassword));
            $session->save();

            $cookie = new Cookie($session->getName(), $session->getId());
            $client->getCookieJar()->set($cookie);
        }
    }

    /**
     * @return RestClient
     */
    public function createFrontClient()
    {
        return new FrontClient($this->getContainer());
    }

    /**
     * @return void
     */
    public function cleanMailDir()
    {
        $this->swiftmailerCacheUtility->cleanMailDir();
    }

    /**
     * @return RestClient
     */
    public function createRestClient()
    {
        return new RestClient($this->getContainer());
    }

    /**
     * @param string $reference
     *
     * @return object|null
     */
    public function getRealEntity($reference)
    {
        $entity = self::$fixtures->getReference($reference);

        return $this->em->find(get_class($entity), $entity->getId());
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return FixtureUtility::$namespace;
    }

    /**
     * Get the container
     *
     * @return Container
     */
    public static function getStaticContainer()
    {
        return ContainerUtility::getContainer('logical');
    }

    /**
     * @param string|null $namespace
     *
     * @return void
     */
    public static function loadDefaultFixtures($namespace = null)
    {
        self::$defaultFixtures = FixtureUtility::loadDefaultFixtures($namespace);
    }

    /**
     * @param string $context
     *
     * @return void
     */
    public static function loadFixturesByContext($context)
    {
        $defaultFixtures = NamespaceUtility::getClassNamesByContext(FixtureUtility::$namespace, $context);
        self::loadStaticFixtures($defaultFixtures);
    }

    /**
     * @param array $classNames List of fully qualified class names of fixtures to load
     *
     * @return void
     */
    public function loadPartialFixtures(array $classNames)
    {
        self::$fixtures = FixtureUtility::loadPartialFixtures($classNames, $this->em)->getReferenceRepository();
    }

    /**
     * @param string $context
     *
     * @return void
     */
    public function loadPartialFixturesByContext($context)
    {
        $classNames = NamespaceUtility::getClassNamesByContext(FixtureUtility::$namespace, $context);
        self::$fixtures = FixtureUtility::loadPartialFixtures($classNames, $this->em)->getReferenceRepository();
    }

    /**
     * @param array $classNames
     * @param int   $purgeMode
     *
     * @return void
     */
    public static function loadStaticFixtures(array $classNames, $purgeMode = ORMPurger::PURGE_MODE_TRUNCATE)
    {
        $hashFixtures = md5(serialize($classNames));

        if ($hashFixtures != self::$hashFixtures) {
            self::$hashFixtures = $hashFixtures;
            self::$fixtures = FixtureUtility::loadFixtures($classNames, 'logical', $purgeMode)->getReferenceRepository();
        }
        
        self::$databaseLoaded = true;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function readMessages()
    {
        return $this->swiftmailerCacheUtility->readMessages();
    }

    /**
     * @return void
     */
    public function resetNamespaceFixtures()
    {
        $file = new \ReflectionClass(get_called_class());
        $name = $file->name;
        $matches = null;
        if (preg_match('/Tests\\\\(.+Bundle)\\\\.*Test/', $name, $matches)) {
            FixtureUtility::$namespace = $matches[1] . '\\';
        }
    }

    /**
     * @param string $namespace Namespace parent of folder DataFixtures (example: 'App\\Bundle\\FrontBundle\\')
     *
     * @return void
     */
    public static function setNamespaceFixtures($namespace)
    {
        FixtureUtility::$namespace = $namespace;
    }

    /**
     * Load empty data fixture to generate the database schema even if no data are given
     *
     * @return void
     */
    public static function setUpBeforeClass()
    {
        $args = func_get_args();
        if (!empty($args)) {
            $datafixtures = $args[0];
        }

        parent::setUpBeforeClass();
        self::$defaultFixtures = array();

        if (self::$iWantDefaultData) {
            self::loadDefaultFixtures();
        } else {
            self::$iWantDefaultData = true;
        }

        if (!empty($datafixtures)) {
            self::$defaultFixtures = array_merge(self::$defaultFixtures, $datafixtures);
        }

        self::loadStaticFixtures(self::$defaultFixtures);
    }

    /**
     * Start transaction
     *
     * @return void
     */
    public function setUp()
    {
        if (self::$databaseLoaded) {
            $this->em->beginTransaction();
        }

        $this->cleanMailDir();

        parent::setUp();
    }

    /**
     * Close connection to avoid "Too Many Connection" error and rollback transaction
     *
     * @return void
     */
    public function tearDown()
    {
        if (!$this->em->getConnection()->isAutoCommit() && self::$databaseLoaded) {
            $this->em->rollback();
        }

        $this->em->getConnection()->close();

        parent::tearDown();
    }
}
