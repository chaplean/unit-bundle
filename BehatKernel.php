<?php

include 'app/AppKernel.php';

/**
 * Class BehatKernel.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     1.1.1
 */
class BehatKernel extends AppKernel
{
    /**
     * @return string
     */
    public function getLogDir()
    {
        return $this->rootDir.'/../../../../../../var/logs/'.$this->environment;
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return $this->rootDir.'/../../../../../../var/cache/'.$this->environment;
    }
}
