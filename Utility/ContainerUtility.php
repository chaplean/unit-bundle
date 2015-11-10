<?php

namespace Chaplean\Bundle\UnitBundle\Utility;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Kernel;

/**
 * ContainerUtility.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     2.0.0
 */
class ContainerUtility
{
    const BEHAT_KERNEL   = '\BehatKernel';
    const DEFAULT_KERNEL = '\AppKernel';

    /**
     * @var Container
     */
    private static $container;

    /**
     * Load container
     *
     * @param string $typeTest Define a type of test (logical, functionnal or behat)
     *
     * @return void
     */
    private static function loadContainer($typeTest)
    {
        /** @var Kernel $kernel */

        switch ($typeTest) {
            case 'behat':
                /** @noinspection PhpIncludeInspection */
                require_once 'vendor/chaplean/unit-bundle/Chaplean/Bundle/UnitBundle/BehatKernel.php';
                $kernelClass = self::BEHAT_KERNEL;
                $kernel = new $kernelClass('behat', true);
                break;

            case 'functional':
            case 'logical':
            default:
                $kernelClass = self::DEFAULT_KERNEL;
                $kernel = new $kernelClass('test', true);
                break;
        }

        $kernel->boot();

        self::$container = $kernel->getContainer();
    }

    /**
     * Return the container
     *
     * @param string $typeTest Define a type of test (logical, functionnal or behat)
     *
     * @return Container
     */
    public static function getContainer($typeTest)
    {
        if (empty(self::$container)) {
            self::loadContainer($typeTest);
        }

        return self::$container;
    }
}
