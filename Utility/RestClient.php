<?php

namespace Chaplean\Bundle\UnitBundle\Utility;

/**
 * RestClient.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     2.0.1
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
