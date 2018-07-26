<?php

namespace Chaplean\Bundle\UnitBundle\Utility;

use Composer\Autoload\ClassLoader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * NamespaceUtility.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.coop)
 * @since     2.0.0
 */
class NamespaceUtility
{
    const DIR_DEFAULT_DATA = 'DefaultData';

    private $kernel;

    /**
     * NamespaceUtility constructor.
     *
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @param string $namespace
     * @param string $context
     *
     * @return array
     * @throws \Exception
     */
    public function getClassNamesByContext($namespace, $context = self::DIR_DEFAULT_DATA)
    {
        $defaultFixtures = [];

        try {
            list($namespaceContext, $pathDatafixtures) = $this->getNamespacePathDataFixtures($namespace, $context);
        } catch (\ReflectionException $e) {
            throw new \Exception($e->getMessage());
        }

        if (is_dir($pathDatafixtures)) {
            $files = Finder::create()
                ->files()
                ->in($pathDatafixtures);

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
     * @throws \ReflectionException
     */
    private function getNamespacePathDataFixtures($namespace, $subfolder = '')
    {
        $pathDatafixtures = $this->getBundlePath($namespace) . 'DataFixtures/Liip/' . ($subfolder ? ($subfolder . '/') : $subfolder);
        $namespaceDefaultContext = $namespace . 'DataFixtures\\Liip\\' . ($subfolder ? ($subfolder . '\\') : $subfolder);

        return [$namespaceDefaultContext, $pathDatafixtures];
    }

    /**
     * Returns bundle class name if we're in bundle.
     *
     * @param string $namespace
     *
     * @return mixed
     */
    public function getBundleClassName(string $namespace)
    {
        if (strpos($namespace, 'Bundle') !== false) {
            return str_replace(['\\Bundle', '\\'], '', $namespace);
        }

        return '';
    }

    /**
     * Returns bundle folder path.
     *
     * @param string $namespace
     *
     * @return string
     * @throws \ReflectionException
     */
    public function getBundlePath(string $namespace): string
    {
        $psr4Prefixes = $this->getAutoload()
            ->getPrefixesPsr4();

        if (!array_key_exists($namespace, $psr4Prefixes) || empty($psr4Prefixes[$namespace])) {
            throw new \ReflectionException(sprintf('\'%s\' namespace is not available. Check \'data_fixtures_namespace\' parameter !', $namespace));
        }

        return $psr4Prefixes[$namespace][0];
    }

    /**
     * Returns composer autoload
     */
    public function getAutoload(): ClassLoader
    {
        /** @var ClassLoader $loader */
        $loader = require $this->kernel->getRootDir() . '/../vendor/autoload.php';

        return $loader;
    }
}
