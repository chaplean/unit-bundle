<?php

namespace Chaplean\Bundle\UnitBundle\Utility;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * NamespaceUtility.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
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
    public static function getClassNamesByContext($namespace, $context)
    {
        $defaultFixtures = array();
        try {
            list($namespaceContext, $pathDatafixtures) = self::getNamespacePathDataFixtures($namespace, $context);
        } catch (\ReflectionException $e) {
            throw new \Exception(sprintf('\'%s\' namespace is not available. Check \'data_fixtures_namespace\' parameter !', $namespace));
        }

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
}
