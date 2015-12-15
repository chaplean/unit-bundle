<?php

namespace Chaplean\Bundle\UnitBundle\Fixtures;

use Nelmio\Alice\Fixtures\Fixture;

/**
 * Loader.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     2.0.0
 */
class Loader extends \Nelmio\Alice\Fixtures\Loader
{
    /**
     * parses a file at the given filename
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
