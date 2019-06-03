<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Functional;

use Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase as BaseFunctionalTestCase;

/**
 * Class FunctionalTestCase.
 * Inspiration from Symfony/framework-bundle.
 *
 * @package   Chaplean\Bundle\UnitBundle\Test
 * @author    Tom - Chaplean <tom@chaplean.coop>
 * @copyright 2014 - 2019 Chaplean (https://www.chaplean.coop)
 * @since     9.0.0
 */
class FunctionalTestCase extends BaseFunctionalTestCase
{
    /**
     * @return string
     */
    protected static function getKernelClass()
    {
        require_once __DIR__ . '/app/AppKernel.php';

        return 'Tests\Chaplean\Bundle\UnitBundle\Functional\app\AppKernel';
    }

    /**
     * @param array $options
     *
     * @return mixed|\Symfony\Component\HttpKernel\KernelInterface
     */
    protected static function createKernel(array $options = [])
    {
        $class = self::getKernelClass();

        $testCase = $options['test_case'] ?? 'default';

        return new $class(
            static::getVarDir(),
            $testCase,
            $options['root_config'] ?? 'config.yml',
            $options['environment'] ?? strtolower(static::getVarDir() . $testCase),
            $options['debug'] ?? false
        );
    }

    /**
     * @return string
     */
    protected static function getVarDir()
    {
        return 'CUB' . substr(strrchr(\get_called_class(), '\\'), 1);
    }
}
