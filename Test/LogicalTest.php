<?php

namespace Chaplean\Bundle\UnitBundle\Test;

use Chaplean\Bundle\UnitBundle\Utility\ContainerUtility;
use Chaplean\Bundle\UnitBundle\Utility\FixtureUtility;
use Chaplean\Bundle\UnitBundle\Utility\NamespaceUtility;
use Chaplean\Bundle\UnitBundle\Utility\RestClient;
use Chaplean\Bundle\UnitBundle\Utility\SwiftMailerCacheUtility;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

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
    protected static $hashFixtures;

    /**
     * @var boolean
     */
    protected static $overrideNamespace = false;

    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();

        FixtureUtility::setContainer($this->getContainer());
        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->swiftmailerCacheUtility = $this->getContainer()->get('chaplean_unit.swiftmailer_cache');

        self::resetNamespaceFixtures();
        self::$iWantDefaultData = true;
        self::$overrideNamespace = false;
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
     * @return string
     */
    public static function getCurrentNamespace()
    {
        $file = new \ReflectionClass(get_called_class());
        $name = $file->name;
        $matches = null;
        if (preg_match('/Tests\\\\(.+Bundle)\\\\.*Test/', $name, $matches)) {
            return $matches[1] . '\\';
        }

        return '';
    }

    /**
     * @param string $reference
     *
     * @return null|object
     */
    public function getEntity($reference)
    {
        $entity = self::$fixtures->getReference($reference);

        return $this->em->find(ClassUtils::getClass($entity), $entity->getId());
    }

    /**
     * @return EntityManager
     */
    public function getManager()
    {
        return $this->em;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return FixtureUtility::$namespace;
    }

    /**
     * @param string $reference
     *
     * @return object|null
     * @deprecated Use getEntity now !
     */
    public function getRealEntity($reference)
    {
        return $this->getEntity($reference);
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
     * Get the container
     *
     * @return Container
     * @deprecated Is too dangerous !
     */
    public static function getStaticContainer()
    {
        return ContainerUtility::getContainer('logical');
    }

    /**
     * @return boolean
     */
    public function isOverrideNamespace()
    {
        return self::$overrideNamespace;
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
            self::$fixtures = FixtureUtility::loadFixtures($classNames, $purgeMode)->getReferenceRepository();
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
    public static function resetNamespaceFixtures()
    {
        FixtureUtility::$namespace = self::getCurrentNamespace();
    }

    /**
     * @param boolean $iWantDefaultData
     *
     * @return void
     */
    public static function setIWantDefaultData($iWantDefaultData)
    {
        self::$iWantDefaultData = $iWantDefaultData;
    }

    /**
     * @param string $namespace Namespace parent of folder DataFixtures (example: 'App\\Bundle\\FrontBundle\\')
     *
     * @return void
     */
    public static function setNamespaceFixtures($namespace)
    {
        self::$overrideNamespace = true;
        FixtureUtility::$namespace = $namespace;
    }

    /**
     * @return void
     */
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        self::$overrideNamespace = false;
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

        if (!self::$overrideNamespace && FixtureUtility::$namespace != self::getCurrentNamespace()) {
            self::resetNamespaceFixtures();
        }

        parent::setUpBeforeClass();
        self::$defaultFixtures = array();

        if (self::$iWantDefaultData) {
            self::loadDefaultFixtures();
        } else {
            self::$iWantDefaultData = true;
        }

        if (isset($datafixtures)) {
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
        if ($this->em->getConnection()->getTransactionNestingLevel() > 0 && self::$databaseLoaded) {
            $this->em->rollback();
        }

        $this->em->getConnection()->close();

        parent::tearDown();
    }
}
