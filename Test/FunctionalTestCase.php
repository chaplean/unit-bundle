<?php

namespace Chaplean\Bundle\UnitBundle\Test {

    use Chaplean\Bundle\UnitBundle\DataFixtures\ProxyReferenceRepository;
    use Chaplean\Bundle\UnitBundle\TextUI\Output;
    use Chaplean\Bundle\UnitBundle\Utility\FixtureLiteUtility;
    use Chaplean\Bundle\UnitBundle\Utility\NamespaceUtility;
    use Chaplean\Bundle\UnitBundle\Utility\Timer;
    use Doctrine\Common\Persistence\ObjectManager;
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Bundle\FrameworkBundle\Console\Application;
    use Symfony\Bundle\FrameworkBundle\KernelBrowser;
    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
    use Symfony\Component\BrowserKit\Cookie;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Tester\CommandTester;
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
     * @property EntityManagerInterface em Entity Manager
     */
    class FunctionalTestCase extends WebTestCase
    {
        /**
         * @var KernelBrowser
         */
        protected static $client;

        /**
         * @var boolean
         */
        private static $databaseLoaded = false;

        /**
         * @var ProxyReferenceRepository
         */
        private static $fixtures;

        /**
         * @var FixtureLiteUtility
         */
        private static $fixtureUtility;

        /**
         * @var boolean
         */
        private static $hasReferenceLoaded = false;

        /**
         * @var boolean
         */
        private static $reloadDatabase = false;

        /**
         * @var array
         */
        private static $userRoles = [];

        /**
         * @param string $name Property name.
         *
         * @return ObjectManager
         * @throws \Exception
         */
        public function __get(string $name)
        {
            if ($name === 'em') {
                return static::getDefaultEntityManager();
            }

            throw new \Exception('Undefined property ' . $name);
        }

        /**
         * @param string|object $user
         * @param KernelBrowser $client
         *
         * @return void
         * @throws \Exception
         */
        public function authenticate($user, KernelBrowser $client = null)
        {
            $usernameTokenPassword = new UsernamePasswordToken($user, null, 'main', $user->getRoles());

            try {
                $this->getContainer()
                    ->get('security.token_storage')
                    ->setToken($usernameTokenPassword);
            } catch (ServiceNotFoundException $e) {
                throw new \LogicException("You can't authenticate as you don't have the \"security.token_storage\" service in your container.");
            }

            if ($client !== null) {
                $clientContainer = $client->getContainer();

                $clientContainer->get('security.token_storage')
                    ->setToken($usernameTokenPassword);

                /** @var Session $session */
                $session = $clientContainer->get('session');

                $session->set('_security_main', serialize($usernameTokenPassword));
                $session->save();

                $cookie = new Cookie($session->getName(), $session->getId());
                $client->getCookieJar()
                    ->set($cookie);
            }
        }

        /**
         * @param array $options
         *
         * @return \Symfony\Component\HttpKernel\KernelInterface|void
         * @throws \Exception
         */
        protected static function bootKernel(array $options = [])
        {
            $options['environment'] = 'test';
            parent::bootKernel($options);

            static::mockServices(static::$container);

            static::loadFixtures();
            static::loadUserRoles();

            /** @var EntityManagerInterface $em */
            $em = static::$container
                ->get('doctrine')
                ->getManager();

            static::enableTransactions($em);

            return static::$kernel;
        }

        /**
         * @param array $options
         * @param array $server
         *
         * @return KernelBrowser
         * @throws \Doctrine\DBAL\ConnectionException
         * @throws \Doctrine\DBAL\DBALException
         */
        protected static function createClient(array $options = [], array $server = []): KernelBrowser
        {
            // Prevent double client creation in same test case
            if (static::$client !== null) {
                return static::$client;
            }

            if (self::$hasReferenceLoaded) {
                throw new \RuntimeException('You must create client before the first getReference in your test');
            }

            static::$client = parent::createClient($options, $server);

            /** @var EntityManagerInterface $em */
            $em = static::$client->getContainer()
                ->get('doctrine')
                ->getManager();

            static::enableTransactions($em);
            static::mockServices(static::$client->getContainer());

            return static::$client;
        }

        /**
         * @param string $userReference
         *
         * @return KernelBrowser
         * @throws \Exception
         */
        public function createClientWith(string $userReference): KernelBrowser
        {
            $client = static::createClient();

            if ($userReference !== '') {
                $this->authenticate($this->getReference($userReference), $client);
            }

            return $client;
        }

        /**
         * @param string $commandName The name of the command, like 'app:create-user'
         *
         * @return CommandTester
         * @throws \Exception
         */
        public function createCommandTester(string $commandName): CommandTester
        {
            $application = new Application(
                $this->getContainer()
                    ->get('kernel')
            );

            /** @var Command $command */
            $command = $application->find($commandName);

            return new CommandTester($command);
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
            $em->getUnitOfWork()
                ->clear();

            $nbTransactions = $em->getConnection()
                ->getTransactionNestingLevel();

            $em->getConnection()
                ->query('PRAGMA foreign_keys = ON;');

            if (self::$databaseLoaded && $nbTransactions < 1) {
                $em->getConnection()
                    ->setNestTransactionsWithSavepoints(true);

                $em->beginTransaction();
            }
        }

        /**
         * @return ContainerInterface
         * @throws \Exception
         */
        protected function getContainer(): ?ContainerInterface
        {
            $container = static::$container;

            // If we have client container, we prefer this one
            if (static::$client !== null) {
                $container = static::$client->getContainer();
            }

            if ($container === null) {
                throw new \Exception('You can\'t use getContainer() if you haven\'t use bootKernel() or createKernel() before');
            }

            return $container;
        }

        /**
         * Get the default Entity Manager: from client container if exist, otherwise from current container.
         *
         * @return EntityManagerInterface|null
         * @throws \Exception
         */
        private static function getDefaultEntityManager(): ?EntityManagerInterface
        {
            if (static::$client !== null) {
                return static::$client
                    ->getContainer()
                    ->get('doctrine')
                    ->getManager();
            } else {
                if (static::$container !== null) {
                    return static::$container
                        ->get('doctrine')
                        ->getManager();
                }
            }

            return null;
        }

        /**
         * @param string        $formClass
         * @param KernelBrowser $client
         *
         * @return string
         * @throws \Exception
         */
        public function getCsrfToken(string $formClass, KernelBrowser $client = null): string
        {
            $client = $client ? : $this;

            if (!$client->getContainer()
                ->has('form.factory')) {
                throw new \LogicException("You can't get CSRF Token as you don't have the \"form.factory\" service in your container.");
            }

            /** @var Form $form */
            $form = $client->getContainer()
                ->get('form.factory')
                ->create($formClass);
            $fields = $form->createView()->children;

            if (!array_key_exists('_token', $fields)) {
                throw new \Exception('CrsfToken disabled');
            }

            return $fields['_token']->vars['value'];
        }

        /**
         * @return string
         */
        public static function getDefaultFixturesNamespace(): ?string
        {
            return static::$container->getParameter('chaplean_unit.data_fixtures_namespace');
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
         * @throws \ReflectionException
         */
        public function getNotPublicMethod(string $className, string $methodName): \ReflectionMethod
        {
            $class = new \ReflectionClass($className);
            $method = $class->getMethod($methodName);
            $method->setAccessible(true);

            return $method;
        }

        /**
         * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
         *
         * @return array
         */
        private static function getOtherMockedServices(ContainerInterface $container): array
        {
            /** @var \Chaplean\Bundle\UnitBundle\Mock\MockedServiceOnSetUpInterface $mockedServices */
            $mockedServices = $container->getParameter('chaplean_unit.mocked_services');

            if ($mockedServices === null) {
                return [];
            }

            return $mockedServices::getMockedServices();
        }

        /**
         * @param string $reference
         *
         * @return object
         * @throws \Doctrine\ORM\ORMException
         * @throws \Exception
         */
        public function getReference(string $reference)
        {
            if (self::$fixtures === null) {
                return null;
            }

            self::$hasReferenceLoaded = true;

            return self::$fixtures->getReferenceWithManager($reference, self::getDefaultEntityManager());
        }

        /**
         * @return void
         */
        public static function loadFixtures(): void
        {
            if (self::$databaseLoaded && !self::$reloadDatabase) {
                return;
            }

            $defaultNamespace = static::getDefaultFixturesNamespace();

            if ($defaultNamespace === null) {
                return;
            }

            if (!self::$reloadDatabase) {
                echo Output::info("Database initialization...\n\n");
                Timer::start();
            }

            try {
                $namespaceUtility = new NamespaceUtility(static::$container->get('kernel'));

                if (self::$fixtureUtility === null) {
                    self::$fixtureUtility = FixtureLiteUtility::getInstance(static::$container);
                }

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

            self::$databaseLoaded = true;
            self::$reloadDatabase = false;
        }

        /**
         * @return void
         */
        public static function loadUserRoles(): void
        {
            try {
                self::$userRoles = static::$container
                    ->getParameter('test_roles');
            } catch (\InvalidArgumentException $e) {
                self::$userRoles = [];
            }
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
                $knpPdf->shouldReceive('getOutputFromHtml')
                    ->andReturn($pdf);
                $knpPdf->shouldReceive('getOutput')
                    ->andReturn($pdf);

                $servicesToOverride['knp_snappy.pdf'] = $knpPdf;
            }

            $servicesToOverride = array_merge($servicesToOverride, static::getOtherMockedServices($container));

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
        public function rolesProvider(array $expectations, array $extraRoles = []): array
        {
            if (empty(self::$userRoles)) {
                throw new \LogicException('You must define test_roles in your parameters_test.yml to use this function.');
            }

            $countExpectations = count($expectations);
            $rolesNames = array_keys(self::$userRoles);
            $countRoles = count($rolesNames);

            if ($countExpectations !== $countRoles) {
                throw new \LogicException(
                    sprintf(
                        'The number of expectations (%d) must match the number of roles (%d)',
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
                array_values(self::$userRoles),
                array_values($expectations)
            );

            return array_merge(array_combine($rolesNames, $mapUserExpectation), $extraRoles);
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
         * @return void
         * @throws \Exception
         */
        protected function tearDown(): void
        {
            self::$hasReferenceLoaded = false;

            if (static::$client !== null) {
                if (static::$client->getContainer() !== null) {
                    /** @var EntityManagerInterface $em */
                    $em = static::$client->getContainer()
                        ->get('doctrine')
                        ->getManager();

                    static::rollbackTransactions($em);
                }

                static::$client = null;
            }

            if ($this->em !== null) {
                static::rollbackTransactions($this->em);
            }

            parent::tearDown();
        }
    }
}

// Hack needed cause SF 4.3 renamed Client to KernelBrowser.
// As we want compatibility with SF 3.X, we creates KernelBrowser if it doesn't exist...
namespace Symfony\Bundle\FrameworkBundle {

    if (!class_exists('Symfony\Bundle\FrameworkBundle\KernelBrowser')) {
        class KernelBrowser extends Client
        {
        }

        ;
    }
}
