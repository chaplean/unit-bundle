<?php

namespace Chaplean\Bundle\UnitBundle\Test;

use Chaplean\Bundle\UnitBundle\DataFixtures\ProxyReferenceRepository;
use Chaplean\Bundle\UnitBundle\TextUI\Output;
use Chaplean\Bundle\UnitBundle\Utility\FixtureLiteUtility;
use Chaplean\Bundle\UnitBundle\Utility\NamespaceUtility;
use Chaplean\Bundle\UnitBundle\Utility\RestClient;
use Chaplean\Bundle\UnitBundle\Utility\Timer;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Class FunctionnalTestCase.
 *
 * @package   Chaplean\Bundle\UnitBundle\Test
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2017 Chaplean (https://www.chaplean.coop)
 * @since     7.0.0
 *
 * @property EntityManager em Entity Manager
 */
class FunctionalTestCase extends WebTestCase
{
    /**
     * @var array
     */
    protected $containers = [];

    /**
     * @var Container
     */
    protected static $container;

    /**
     * @var boolean
     */
    private static $databaseLoaded = false;

    /**
     * @var boolean
     */
    private static $reloadDatabase = false;

    /**
     * @var FixtureLiteUtility
     */
    private static $fixtureUtility;

    /**
     * @var ProxyReferenceRepository
     */
    private static $fixtures;

    /**
     * @var Client
     */
    private static $client;

    /**
     * @var array
     */
    private $userRoles;

    /**
     * FunctionalTestCase constructor.
     *
     * @param null   $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        if (self::$container === null) {
            self::$container = $this->initializeContainer();
        }

        if (self::$fixtureUtility === null) {
            self::$fixtureUtility = FixtureLiteUtility::getInstance(self::$container);
        }

        try {
            $this->userRoles = $this->getContainer()->getParameter('test_roles');
        } catch (\InvalidArgumentException $e) {
            $this->userRoles = [];
        }
    }

    /**
     * @return ContainerInterface
     */
    public function initializeContainer(): ContainerInterface
    {
        $cacheKey = '|test';
        if (empty($this->containers[$cacheKey])) {
            $options = [
                'environment' => 'test',
            ];
            $kernel = $this->createKernel($options);
            $kernel->boot();

            $this->containers[$cacheKey] = $kernel->getContainer();
        }

        if (isset($tmpKernelDir)) {
            $_SERVER['KERNEL_DIR'] = $tmpKernelDir;
        }

        return $this->containers[$cacheKey];
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
     * Reset all non-important services (Important service: kernel, doctrine (+ related service) and orm (+ related service))
     *
     * @param Container|ContainerInterface $container
     *
     * @return void
     */
    private static function clearContainer(ContainerInterface $container)
    {
        foreach ($container->getServiceIds() as $service) {
            if (!preg_match_all('/(kernel|doctrine|[^f]orm|service_container)/', $service)) {
                self::$container->set($service, null);
            }
        }
    }

    /**
     * @param array $options
     * @param array $server
     *
     * @return Client
     */
    public static function createClient(array $options = [], array $server = [])
    {
        self::$client = parent::createClient($options, $server);
        $em = self::$client->getContainer()->get('doctrine')->getManager();

        self::enableTransactions($em);
        self::mockServices(self::$client->getContainer());

        return self::$client;
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
     * @param string $commandClass
     *
     * @return CommandTester
     */
    public function createCommandTester($commandClass)
    {
        $command = new $commandClass();

        $application = new Application();
        $application->add($command);

        /** @var ContainerAwareCommand $command */
        $command = $application->find($command->getName());
        $command->setContainer($this->getContainer());

        return new CommandTester($command);
    }

    /**
     * @return RestClient
     * @deprecated Will be remove in next version
     */
    public function createRestClient()
    {
        return new RestClient($this->getContainer());
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        if (self::$client !== null) {
            return self::$client->getContainer();
        }

        return self::$container;
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
            $dataFixtureNamespace = self::$container->getParameter('data_fixtures_namespace');

            if ($dataFixtureNamespace === false) {
                return null;
            }

            return $dataFixtureNamespace;
        } catch (\InvalidArgumentException $e) {
            return 'App\Bundle\RestBundle\\';
        }
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
    public function getReference(string $reference)
    {
        if (self::$fixtures === null) {
            return null;
        }

        $manager = null;

        if (self::$client !== null) {
            $manager = self::$client->getContainer()->get('doctrine')->getManager();
        }

        return self::$fixtures->getReferenceWithManager($reference, $manager);
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     *
     * @return array
     */
    private static function getOtherMockedServices(ContainerInterface $container)
    {
        /** @var \Chaplean\Bundle\UnitBundle\Mock\MockedServiceOnSetUpInterface $mockedServices */
        $mockedServices = $container->getParameter('chaplean_unit.mocked_services');

        if ($mockedServices === null) {
            return [];
        }

        return $mockedServices::getMockedServices();
    }

    /**
     * @param string $name Property name.
     *
     * @return \Doctrine\ORM\EntityManager
     * @throws \Exception
     */
    public function __get($name)
    {
        if ($name === 'em') {
            if (self::$client !== null) {
                return self::$client->getContainer()->get('doctrine')->getManager();
            } else {
                return $this->getContainer()->get('doctrine')->getManager();
            }
        }

        throw new \Exception('Undefined property ' . $name);
    }

    /**
     * @param ContainerInterface $container
     *
     * @return mixed
     */
    private static function mockServices(ContainerInterface $container)
    {
        $servicesToOverride = [];

        if (class_exists('Knp\Bundle\SnappyBundle\Snappy\LoggableGenerator')) {
            $pdf = file_get_contents(__DIR__ . '/../Resources/pdf.pdf');

            $knpPdf = \Mockery::mock('Knp\Bundle\SnappyBundle\Snappy\LoggableGenerator');
            $knpPdf->shouldReceive('getOutputFromHtml')->andReturn($pdf);
            $knpPdf->shouldReceive('getOutput')->andReturn($pdf);

            $servicesToOverride['knp_snappy.pdf'] = $knpPdf;
        }

        $servicesToOverride = array_merge($servicesToOverride, self::getOtherMockedServices($container));

        foreach ($servicesToOverride as $service => $mock) {
            $container->set($service, $mock);
        }

        return $container;
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
            throw new \LogicException('You must define test_roles in your parameters_test.yml to use this function.');
        }

        $countExpectations = count($expectations);
        $rolesNames = array_keys($this->userRoles);
        $countRoles = count($rolesNames);

        if ($countExpectations !== $countRoles) {
            throw new \LogicException(
                sprintf(
                    'The number of expecations (%d) must match the number of roles (%d)',
                    $countExpectations,
                    $countRoles
                )
            );
        }

        if ($rolesNames !== array_keys($expectations)) {
            throw new \LogicException('The roles in the expectations given don\'t match the existing roles');
        }

        $mapUserExpectation = array_map(
            function ($userReference, $expectation) {
                if (is_array($expectation)) {
                    array_unshift($expectation, $userReference);

                    return $expectation;
                }

                return [$userReference, $expectation];
            },
            array_values($this->userRoles),
            array_values($expectations)
        );

        return array_merge(array_combine($rolesNames, $mapUserExpectation), $extraRoles);
    }

    /**
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        self::enableTransactions($this->em);
        self::mockServices($this->getContainer());
    }

    /**
     * @return void
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $defaultNamespace = self::getDefaultFixturesNamespace();

        if ((!self::$databaseLoaded || self::$reloadDatabase) && $defaultNamespace !== null) {
            self::mockServices(self::$container);

            if (!self::$reloadDatabase) {
                echo Output::info("Initialization database...");
                Timer::start();
            }

            try {
                $namespaceUtility = new NamespaceUtility(self::$container->get('kernel'));

                self::$fixtures = self::$fixtureUtility
                    ->loadFixtures($namespaceUtility->getClassNamesByContext($defaultNamespace))
                    ->getReferenceRepository();
            } catch (\Exception $e) {
                echo Output::danger($e->getMessage() . "\n\n");
            }

            if (!self::$reloadDatabase) {
                try {
                    echo sprintf(" Done %s (%s)\n\n", Output::success(Output::CHAR_CHECK), Timer::toString(Timer::stop()));
                } catch (\Exception $e) {
                    // Timer not started : not a big issue
                }
            }

            self::clearContainer(self::$container);
            self::$databaseLoaded = true;
            self::$reloadDatabase = false;
        }
    }

    /**
     * @return void
     */
    protected function tearDown()
    {
        if (self::$client !== null) {
            $em = self::$client->getContainer()->get('doctrine')->getManager();
            self::rollbackTransactions($em);

            self::$client = null;
        }

        parent::tearDown();

        self::rollbackTransactions($this->em);

        // Unauthenticate user between each test
        if ($this->getContainer() !== null) {
            try {
                $this->getContainer()->get('security.token_storage')->setToken(null);
            } catch (ServiceNotFoundException $e) {
                // Intentionnaly empty
            }
        }

        self::clearContainer($this->getContainer());
    }

    /**
     * @param EntityManagerInterface $em
     *
     * @return void
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     */
    private static function enableTransactions(EntityManagerInterface $em): void
    {
        $em->getUnitOfWork()->clear();
        $nbTransactions = $em->getConnection()->getTransactionNestingLevel();

        $em->getConnection()->query('PRAGMA foreign_keys = ON;');

        if (self::$databaseLoaded && $nbTransactions < 1) {
            $em->getConnection()->setNestTransactionsWithSavepoints(true);
            $em->beginTransaction();
        }
    }

    /**
     * @param EntityManagerInterface $em
     *
     * @return void
     */
    private static function rollbackTransactions(EntityManagerInterface $em): void
    {
        $connection = $em->getConnection();

        if ($connection->getTransactionNestingLevel() == 1 && self::$databaseLoaded) {
            $em->rollback();
        }

        $connection->close();
    }

    /**
     * @inheritdoc
     * @deprecated Use 'createCommandTester' instead (Will be removed in next version)
     *
     * @codeCoverageIgnore
     */
    protected function runCommand($name, array $params = [], $reuseKernel = true)
    {
        return parent::runCommand($name, $params, $reuseKernel);
    }
}
