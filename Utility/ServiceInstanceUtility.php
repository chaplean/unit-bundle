<?php

namespace Chaplean\Bundle\UnitBundle\Utility;

/**
 * Class ServiceInstanceUtility.
 *
 * @package   Chaplean\Bundle\UnitBundle\Utility
 * @author    Tom - Chaplean <tom@chaplean.com>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.com)
 * @since     4.2.0
 */
class ServiceInstanceUtility
{
    /**
     * @var SwiftMailerCacheUtility
     */
    private $swiftMailerCache;

    /**
     * ServiceInstanceUtility constructor.
     *
     * @param SwiftMailerCacheUtility $swiftMailerCache
     */
    public function __construct(SwiftMailerCacheUtility $swiftMailerCache)
    {
        $this->swiftMailerCache = $swiftMailerCache;
    }

    /**
     * @return SwiftMailerCacheUtility
     */
    public function getSwiftMailerCacheClass()
    {
        return get_class($this->swiftMailerCache);
    }
}
