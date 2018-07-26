<?php

namespace Chaplean\Bundle\UnitBundle\Utility;

use Composer\Autoload\ClassLoader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

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

    /**
     * @param string $namespace
     * @param string $context
     *
     * @return array
     * @throws \Exception
     */
    public static function getClassNamesByContext($namespace, $context = self::DIR_DEFAULT_DATA)
    {
        $defaultFixtures = [];

        try {
            list($namespaceContext, $pathDatafixtures) = self::getNamespacePathDataFixtures($namespace, $context);
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
    private static function getNamespacePathDataFixtures($namespace, $subfolder = '')
    {
        $pathDatafixtures = self::getBundlePath($namespace) . 'DataFixtures/Liip/' . ($subfolder ? ($subfolder . '/') : $subfolder);
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
    public static function getBundleClassName(string $namespace)
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
    public static function getBundlePath(string $namespace): string
    {
        $psr4Prefixes = self::getAutoload()
            ->getPrefixesPsr4();

        if (!array_key_exists($namespace, $psr4Prefixes) || empty($psr4Prefixes[$namespace])) {
            throw new \ReflectionException(sprintf('\'%s\' namespace is not available. Check \'data_fixtures_namespace\' parameter !', $namespace));
        }

        return $psr4Prefixes[$namespace][0];
    }

    /**
     * Returns composer autoload
     */
    public static function getAutoload(): ClassLoader
    {
        /** @var ClassLoader $loader */
        $loader = require __DIR__ . '/../vendor/autoload.php';

        return $loader;
    }
}
