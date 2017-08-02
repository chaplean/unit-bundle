<?php

namespace Chaplean\Bundle\UnitBundle\Utility;

/**
 * Class ServiceShared.
 *
 * @package   Chaplean\Bundle\UnitBundle\Utility
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.coop)
 * @since     4.3.0
 */
class SharedService
{
    /**
     * @return string
     */
    public function mockMe()
    {
        return 'I am not mocked';
    }
}
