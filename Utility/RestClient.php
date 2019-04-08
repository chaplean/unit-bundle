<?php

namespace Chaplean\Bundle\UnitBundle\Utility;

/**
 * RestClient.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2015 Chaplean (https://www.chaplean.coop)
 * @since     2.0.1
 *
 * @deprecated Will be removed in next major version
 */
class RestClient extends Client
{
    /**
     * Convert to array json response of REST
     *
     * @return mixed|null
     *
     * @throws \Exception
     */
    public function getContent()
    {
        if (empty($this->response)) {
            throw new \Exception('Not response flush !');
        }

        return json_decode($this->response->getContent(), true);
    }
}
