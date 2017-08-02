<?php

namespace Chaplean\Bundle\UnitBundle\Utility;

/**
 * Class ServiceDependentSharedService.
 *
 * @package   Chaplean\Bundle\UnitBundle\Utility
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.coop)
 * @since     4.2.0
 */
class ServiceDependentSharedService
{
    /**
     * @var SharedService
     */
    private $serviceShared;

    /**
     * ServiceDependentSharedService constructor.
     *
     * @param SharedService $serviceShared
     */
    public function __construct(SharedService $serviceShared)
    {
        $this->serviceShared = $serviceShared;
    }

    /**
     * @return string
     */
    public function callMockMe()
    {
        return $this->serviceShared->mockMe();
    }

    /**
     * @return string
     */
    public function dontCallMockMe()
    {
        return 'eenie meenie miney mo';
    }
}
