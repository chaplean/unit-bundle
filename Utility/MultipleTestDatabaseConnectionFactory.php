<?php

namespace Chaplean\Bundle\UnitBundle\Utility;

use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;

/**
 * Class MultipleTestDatabaseConnectionFactory.
 *
 * @package   Chaplean\Bundle\UnitBundle\Utility
 * @author    Matthias - Chaplean <matthias@chaplean.coop>
 * @copyright 2014 - 2017 Chaplean (https://www.chaplean.coop)
 * @since     7.0.0
 */
class MultipleTestDatabaseConnectionFactory extends ConnectionFactory
{
    /**
     * Override the database path if the TEST_TOKEN env var is set (to use with paratest).
     *
     * @param array              $params
     * @param Configuration|null $config
     * @param EventManager|null  $eventManager
     * @param array              $mappingTypes
     *
     * @return \Doctrine\DBAL\Connection
     */
    public function createConnection(array $params, Configuration $config = null, EventManager $eventManager = null, array $mappingTypes = [])
    {
        if (\getenv('TEST_TOKEN') !== false) {
            $testToken = \getenv('TEST_TOKEN');

            $params['path'] = $params['path'] . $testToken;
        }

        return parent::createConnection($params, $config, $eventManager,$mappingTypes);
    }

}
