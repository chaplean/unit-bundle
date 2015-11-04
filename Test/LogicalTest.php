<?php

namespace Chaplean\Bundle\UnitBundle\Test;

use Chaplean\Bundle\UnitBundle\Utility\FixtureUtility;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Entity;
use Doctrine\ORM\EntityManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * LogicalTest.
 *
 * @author    Matthias - Chaplean <matthias@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     0.1.0
 */
class LogicalTest extends WebTestCase
{
    const DIR_DEFAULT_DATA = 'DefaultData';

    /**
     * @var EntityManager
     */
    protected $em;

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
    protected static $iWantDefaultData = true;

    protected static $namespace;

    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();

        $this->em = $this->getContainer()->get('doctrine')->getManager();

        $file = new \ReflectionClass(get_called_class());
        $name = $file->getName();
        self::$namespace = substr($name, 0, strpos($name, 'Tests'));
    }

    /**
     * @return void
     */
    public function cleanMailDir()
    {
        $mailDir = $this->getContainer()->getParameter('kernel.cache_dir') . '/swiftmailer/spool/default';

        if (is_dir($mailDir)) {
            $finder = Finder::create()->files()->in($mailDir);

            /** @var SplFileInfo $file */
            foreach ($finder as $file) {
                unlink($file->getRealPath());
            }
        }
    }

    /**
     * @param string $context
     *
     * @return array
     */
    private static function getClassNamesByContext($context)
    {
        $defaultFixtures = array();
        list($namespaceContext, $pathDatafixtures) = self::getNamespacePathDataFixtures(self::$namespace, $context);

        if (is_dir($pathDatafixtures)) {
            $files = Finder::create()->files()->in($pathDatafixtures);

            /** @var SplFileInfo $file */
            foreach ($files as $file) {
                $defaultFixtures[] = $namespaceContext . str_replace('.php', '', $file->getFilename());
            }
        }

        return $defaultFixtures;
    }

    /**
     * @param string $namespace
     * @param string $subfolder
     *
     * @return array
     */
    private static function getNamespacePathDataFixtures($namespace, $subfolder = '')
    {
        $classBundleName = str_replace(array('\\Bundle', '\\'), '', $namespace);
        $classBundle = new \ReflectionClass($namespace . $classBundleName);
        $path = str_replace($classBundleName . '.php', '', $classBundle->getFileName());
        $pathDatafixtures = $path . 'DataFixtures/Liip/' . ($subfolder ? ($subfolder . '/') : $subfolder);
        $namespaceDefaultContext = $namespace . 'DataFixtures\\Liip\\' . ($subfolder ? ($subfolder . '\\') : $subfolder);

        return array($namespaceDefaultContext, $pathDatafixtures);
    }

    /**
     * @param string $reference
     *
     * @return Entity
     */
    public function getRealEntity($reference)
    {
        $entity = self::$fixtures->getReference($reference);

        return $this->em->find(get_class($entity), $entity->getId());
    }

    /**
     * Get the container
     *
     * @return Container
     */
    public static function getStaticContainer()
    {
        return FixtureUtility::getContainer('logical');
    }

    /**
     * @param string|null $namespace
     *
     * @return void
     */
    public static function loadDefaultFixtures($namespace = null)
    {
        if (empty($namespace)) {
            $namespace = self::$namespace;
        }

        list($namespaceDefaultContext, $pathDatafixtures) = self::getNamespacePathDataFixtures($namespace, self::DIR_DEFAULT_DATA);

        if (is_dir($pathDatafixtures)) {
            $files = Finder::create()->files()->in($pathDatafixtures);

            /** @var SplFileInfo $file */
            foreach ($files as $file) {
                if ($file->getExtension() == 'php') {
                    self::$defaultFixtures[] = $namespaceDefaultContext . str_replace('.php', '', $file->getFilename());
                }
            }
        }
    }

    /**
     * @param array    $classNames
     * @param string   $omName
     * @param string   $registryName
     * @param int|null $purgeMode
     *
     * @return void
     * @deprecated Use loadStaticFixtures ! Transaction is active by function test
     */
    public function loadFixtures(array $classNames, $omName = null, $registryName = 'doctrine', $purgeMode = ORMPurger::PURGE_MODE_TRUNCATE)
    {
        $omName = null;
        $registryName = null;

        self::$fixtures = FixtureUtility::loadFixtures($classNames, 'logical', $purgeMode)->getReferenceRepository();
    }

    /**
     * @param string $context
     *
     * @return void
     */
    public static function loadFixturesByContext($context)
    {
        $defaultFixtures = self::getClassNamesByContext($context);

        if (!empty($defaultFixtures)) {
            if (empty(self::$fixtures)) {
                self::loadStaticFixtures($defaultFixtures);
            } else {
                self::$fixtures = FixtureUtility::loadPartialFixtures($defaultFixtures, null)->getReferenceRepository();
            }
        }
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
        $classNames = self::getClassNamesByContext($context);
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
        self::$fixtures = FixtureUtility::loadFixtures($classNames, 'logical', $purgeMode)->getReferenceRepository();
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function readMessages()
    {
        $messages = array();
        $mailDir = $this->getContainer()->getParameter('kernel.cache_dir') . '/swiftmailer/spool/default';
        $finder = Finder::create()->files()->in($mailDir);

        if ($finder->count() == 0) {
            return null;
        }

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $messages[] = unserialize($file->getContents());
        }

        return count($messages) == 1 ? $messages[0] : $messages;
    }

    /**
     * Load empty data fixture to generate the database schema even if no data are given
     *
     * @return void
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$defaultFixtures = array();

        if (self::$iWantDefaultData) {
            self::loadDefaultFixtures();
        } else {
            self::$iWantDefaultData = true;
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
        $this->em->beginTransaction();
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
        $this->em->rollback();

        $this->em->getConnection()->close();

        parent::tearDown();
    }
}
