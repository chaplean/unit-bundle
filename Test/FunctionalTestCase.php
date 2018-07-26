<?php

namespace Chaplean\Bundle\UnitBundle\Test;

use Chaplean\Bundle\UnitBundle\TextUI\Output;
use Chaplean\Bundle\UnitBundle\Utility\FixtureLiteUtility;
use Chaplean\Bundle\UnitBundle\Utility\NamespaceUtility;
use Chaplean\Bundle\UnitBundle\Utility\RestClient;
use Chaplean\Bundle\UnitBundle\Utility\Timer;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManager;
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
 * @copyright 2014 - 2017 Chaplean (http://www.chaplean.coop)
 * @since     7.0.0
 *
 * @property EntityManager em Entity Manager
 */
class FunctionalTestCase extends WebTestCase
{
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
     * @var ReferenceRepository
     */
    private static $fixtures;

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
            self::$container = parent::getContainer();
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
        $client = parent::createClient($options, $server);
        $client->getContainer()->set('doctrine', self::$container->get('doctrine'));

        self::mockServices($client->getContainer());

        return $client;
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
     */
    public function createRestClient()
    {
        return new RestClient($this->getContainer());
    }

    /**
     * @return Container
     */
    protected function getContainer()
    {
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
    public function getReference($reference)
    {
        if (self::$fixtures === null) {
            return null;
        }

        return self::$fixtures->getReference($reference);
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
            return $this->getContainer()->get('doctrine')->getManager();
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
     * @inheritdoc
     * @deprecated Use 'createCommandTester' instead (Removed in next version)
     *
     * @codeCoverageIgnore
     */
    protected function runCommand($name, array $params = [], $reuseKernel = true)
    {
        return parent::runCommand($name, $params, $reuseKernel);
    }

    /**
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->em->getUnitOfWork()->clear();
        $nbTransactions = $this->em->getConnection()->getTransactionNestingLevel();

        $this->em->getConnection()->query('PRAGMA foreign_keys = ON;');

        if (self::$databaseLoaded && $nbTransactions < 1) {
            $this->em->getConnection()->setNestTransactionsWithSavepoints(true);
            $this->em->beginTransaction();
        }

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

            self::$fixtures = self::$fixtureUtility
                ->loadFixtures(NamespaceUtility::getClassNamesByContext($defaultNamespace))
                ->getReferenceRepository();

            if (!self::$reloadDatabase) {
                echo sprintf(" Done %s (%s)\n\n", Output::success(Output::CHAR_CHECK), Timer::toString(Timer::stop()));
            }

            self::clearContainer(self::$container);
            self::$databaseLoaded = true;
            self::$reloadDatabase = false;
        }
    }

    /**
     * Reload database with classNames only for current test file
     *
     * @param array $classNames
     *
     * @return void
     * @throws \Exception
     */
    public static function reloadFixtures(array $classNames)
    {
        self::$reloadDatabase = true;
        self::$fixtures = self::$fixtureUtility
            ->loadFixtures($classNames, false)
            ->getReferenceRepository();
    }

    /**
     * @return void
     */
    protected function tearDown()
    {
        parent::tearDown();

        if ($this->em !== null) {
            $connection = $this->em->getConnection();

            if ($connection->getTransactionNestingLevel() == 1 && self::$databaseLoaded) {
                $this->em->rollback();
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

        self::clearContainer($this->getContainer());
    }
}
