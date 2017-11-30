<?php

namespace Chaplean\Bundle\UnitBundle\Test;

use Chaplean\Bundle\UnitBundle\Utility\FixtureUtility;
use Chaplean\Bundle\UnitBundle\Utility\NamespaceUtility;
use Chaplean\Bundle\UnitBundle\Utility\RestClient;
use Chaplean\Bundle\UnitBundle\Utility\SwiftMailerCacheUtility;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Mockery\Mock;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * LogicalTestCase.
 *
 * @author    Matthias - Chaplean <matthias@chaplean.coop>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.coop)
 * @since     4.0.0
 *
 * @property EntityManager em Entity Manager.
 */
class LogicalTestCase extends WebTestCase
{
    /**
     * @var ContainerInterface|Container
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
     * @var ArrayCollection
     * @deprecated Remove in 5.1
     */
    private static $servicesToRefresh;

    /**
     * @var SwiftMailerCacheUtility
     */
    private static $swiftmailerCacheUtility = null;

    /**
     * @var array
     */
    private static $userFixtures = [];

    /**
     * @var boolean
     */
    protected static $withDefaultData = true;

    /**
     * @var boolean
     */
    protected static $datafixturesEnabled = true;

    /**
     * @var array
     */
    protected $userRoles;

    /**
     * Construct
     *
     * @param string|null $name
     * @param array       $data
     * @param string      $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        if (self::$fixtureUtility === null) {
            self::$fixtureUtility = FixtureUtility::getInstance();
        }

        if (self::$container === null) {
            $this->setContainer(parent::getContainer());
        }

        if (self::$doctrineRegistry === null) {
            self::$doctrineRegistry = $this->getContainer()->get('doctrine');
        }

        $namespaceFixtures = $this->getFixtureUtility()->getNamespace();

        if (empty($namespaceFixtures)) {
            self::resetDefaultNamespaceFixtures();
        }

        self::$servicesToRefresh = new ArrayCollection();

        try {
            $this->userRoles = $this->getContainer()->getParameter('test_roles');
        } catch (\InvalidArgumentException $e) {
            $this->userRoles = [];
        }
    }

    /**
     * @param $container
     *
     * @return mixed
     */
    private static function overrideContainer($container)
    {
        $servicesToOverride = [];

        if (class_exists('Knp\Bundle\SnappyBundle\Snappy\LoggableGenerator')) {
            $pdf = file_get_contents(__DIR__ . '/../Resources/pdf.pdf');

            $knpPdf = \Mockery::mock('Knp\Bundle\SnappyBundle\Snappy\LoggableGenerator');
            $knpPdf->shouldReceive('getOutputFromHtml')->andReturn($pdf);
            $knpPdf->shouldReceive('getOutput')->andReturn($pdf);

            $servicesToOverride[] = ['knp_snappy.pdf', $knpPdf];
        }

        foreach ($servicesToOverride as $serviceToOverride) {
            $container->set(...$serviceToOverride);
        }

        return $container;
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
        try {
            $this->getContainer()->get('security.token_storage')->setToken($usernameTokenPassword);
        } catch (ServiceNotFoundException $e) {
            throw new \LogicException("You can't authenticate as you don't have the \"security.token_storage\" service in your container.");
        }

        if ($client !== null) {
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
    protected static function createClient(array $options = [], array $server = [])
    {
        $client = parent::createClient($options, $server);
        $client->getContainer()
            ->set('doctrine', self::$doctrineRegistry);

        self::overrideContainer($client->getContainer());

        return $client;
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return self::$container;
    }

    /**
     * @deprecated use getCsrfToken() instead
     *
     * @param string $formClass
     * @param Client $client
     *
     * @return string
     * @throws \Exception
     */
    public function getCrsfToken($formClass, Client $client = null)
    {
        return $this->getCsrfToken($formClass, $client);
    }

    /**
     * @todo Check that symfony/form is installed on project
     *
     * @param string $formClass
     * @param Client $client
     *
     * @return string
     * @throws \Exception
     */
    public function getCsrfToken($formClass, Client $client = null)
    {
        $client = $client ?: $this;

        /** @var Form $form */
        $form = $client->getContainer()->get('form.factory')->create($formClass);
        $fields = $form->createView()->children;

        if (!array_key_exists('_token', $fields)) {
            throw new \Exception('CrsfToken disabled');
        }

        return $fields['_token']->vars['value'];
    }

    /**
     * @return string
     */
    public static function getDefaultFixturesNamespace()
    {
        try {
            $dataFixturesNamespaceParameter = self::$container->getParameter('data_fixtures_namespace');

            if ($dataFixturesNamespaceParameter === false) {
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
        return $this->getFixtureUtility()->getNamespace();
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
     * @param string $className
     * @param string $propertyName
     *
     * @return \ReflectionProperty
     */
    public function getNotPublicProperty($className, $propertyName)
    {
        $class = new \ReflectionClass($className);
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);

        return $property;
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
     * Get a service with refreshed parameters.
     *
     * @param string $serviceName
     *
     * @return mixed
     * @deprecated Useless with the new mock system
     */
    public static function getServiceRefreshed($serviceName)
    {
        self::$container->set($serviceName, null);

        self::$servicesToRefresh->add($serviceName);

        return self::$container->get($serviceName);
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
            ->loadPartialFixtures($classNames, $this->getManager())
            ->getReferenceRepository();
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

        self::$fixtures = $fixtureUtility->loadPartialFixtures($classNames, $this->getManager())
            ->getReferenceRepository();
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
    public static function loadStaticFixtures(array $fixtures = [], $withDefaultData = false)
    {
        self::$userFixtures = $fixtures;
        self::$withDefaultData = $withDefaultData;
    }

    /**
     * @param string $serviceName
     * @param mixed  $instance
     *
     * @return void
     * @deprecated Useless with the new mock system (Set the service directly in the container)
     */
    public static function mockService($serviceName, $instance)
    {
        self::$container
            ->set($serviceName, $instance);

        self::$servicesToRefresh->add($serviceName);
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
     * runCommand with reuseKernel at "true" by default.
     *
     * @param string  $name
     * @param array   $params
     * @param boolean $reuseKernel
     *
     * @return string
     */
    public function runCommand($name, array $params = [], $reuseKernel = true)
    {
        return parent::runCommand($name, $params, $reuseKernel);
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
            $this->getFixtureUtility()->setContainer($container);
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

        $manager->getConnection()->query('PRAGMA foreign_keys = ON;');

        if (self::$databaseLoaded && $nbTransactions < 1) {
            $manager->getConnection()->setNestTransactionsWithSavepoints(true);
            $manager->beginTransaction();
        }

        $this->cleanMailDir();

        self::overrideContainer($this->getContainer());
    }

    /**
     * Load empty data fixture to generate the database schema even if no data are given
     *
     * @return void
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$servicesToRefresh = new ArrayCollection();

        self::$doctrineRegistry = self::$container->get('doctrine');
        self::$swiftmailerCacheUtility = self::$container->get('chaplean_unit.swiftmailer_cache');
        self::$doctrineRegistry->getManager()->getUnitOfWork()->clear();

        $dataFixturesToLoad = self::$userFixtures;

        if (self::$withDefaultData && self::$datafixturesEnabled) {
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
        if ($this->getManager() !== null) {
            $connection = $this->getManager()->getConnection();

            if ($connection->getTransactionNestingLevel() == 1 && self::$databaseLoaded) {
                $this->getManager()->rollback();
            }

            $connection->close();
        }

        // Unauthenticate user between each test
        if ($this->getContainer() !== null) {
            try {
                $this->getContainer()->get('security.token_storage')->setToken(null);
            } catch (ServiceNotFoundException $e) {
                // Intentionnaly empty
            }
        }

        $this->clearContainer();

        parent::tearDown();
    }

    /**
     * @return void
     */
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        self::resetDefaultNamespaceFixtures();

        if (self::$container !== null) {
            foreach (self::$servicesToRefresh as $serviceName) {
                self::$container->set($serviceName, null);
            }
        }

        self::$servicesToRefresh = new ArrayCollection();
        self::$userFixtures = [];
        self::$withDefaultData = true;
    }

    /**
     * Reset all non-important services (Important service: kernel, doctrine (+ related service) and orm (+ related service))
     *
     * @return void
     */
    public function clearContainer()
    {
        foreach ($this->getContainer()->getServiceIds() as $service) {
            if (!preg_match_all('/(kernel|doctrine|[^f]orm|service_container)/', $service)) {
                $this->getContainer()->set($service, null);
            }
        }
    }

    /**
     * @param array $expectations
     * @param array $extraRoles
     *
     * @return array
     */
    public function rolesProvider(array $expectations, array $extraRoles = [])
    {
        if (count($this->userRoles) === 0) {
            throw new \LogicException("You must define test_roles in your parameters_test.yml to use this function.");
        }

        $countExpectations = count($expectations);
        $rolesNames = array_keys($this->userRoles);
        $countRoles = count($rolesNames);

        if ($countExpectations !== $countRoles) {
            throw new \LogicException(
                sprintf(
                    "The number of expecations (%d) must match the number of roles (%d)",
                    $countExpectations,
                    $countRoles
                )
            );
        }

        if ($rolesNames !== array_keys($expectations)) {
            throw new \LogicException('The roles in the expectations given don\'t match the existing roles');
        }

        $mapUserExpectation = array_map(
            function($userReference, $expectation) {
                if (is_array($expectation)) {
                    array_unshift($expectation, $userReference);
                    return $expectation;
                }

                return [$userReference, $expectation];
            }, array_values($this->userRoles), array_values($expectations)
        );

        return array_merge(
            array_combine($rolesNames, $mapUserExpectation),
            $extraRoles
        );
    }

    /**
     * @param string $userReference
     *
     * @return Client
     */
    public function createClientWith($userReference)
    {
        $client = self::createClient();

        if ($userReference !== '') {
            $this->authenticate($this->getReference($userReference), $client);
        }

        return $client;
    }

    /**
     * @param string $input
     *
     * @return resource
     */
    public static function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fputs($stream, $input);
        rewind($stream);

        return $stream;
    }
}
