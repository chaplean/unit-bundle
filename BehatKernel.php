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
    public function __construct($environment, $debug)
    {
        parent::__construct($environment, $debug);

        $r = new \ReflectionObject(new AppKernel($environment, $debug));
        $this->rootDir = str_replace('\\', '/', dirname($r->getFileName()));
    }

    /**
     * @return string
     */
    public function getLogDir()
    {
        return $this->rootDir.'/../var/logs/'.$this->environment;
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return $this->rootDir.'/../var/cache/'.$this->environment;
    }
}
