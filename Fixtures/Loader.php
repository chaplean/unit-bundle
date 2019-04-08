<?php

namespace Chaplean\Bundle\UnitBundle\Fixtures;

use Nelmio\Alice\Fixtures\Fixture;
use Nelmio\Alice\Fixtures\Loader as BaseLoader;

/**
 * Loader.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2015 Chaplean (https://www.chaplean.coop)
 * @since     2.0.0
 */
class Loader extends BaseLoader
{
    /**
     * parent is protected
     *
     * @param string $filename
     *
     * @return array data
     */
    public function parseFile($filename)
    {
        return parent::parseFile($filename);
    }

    /**
     * @param array  $data
     * @param string $fieldName
     *
     * @return mixed
     */
    public function getValue(array $data, $fieldName)
    {
        /** @var Fixture[] $fixtures */
        $fixtures = $this->buildFixtures($data);

        $this->instantiateFixtures($fixtures);
        $this->populator->populate($fixtures[0]);

        return $fixtures[0]->getPropertyValue($fieldName);
    }
}
