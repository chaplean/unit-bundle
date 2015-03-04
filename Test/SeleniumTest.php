<?php
/**
 * SeleniumTest.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     0.1.0
 */

namespace Chaplean\Bundle\UnitBundle\Test;

class SeleniumTest extends \PHPUnit_Extensions_Selenium2TestCase
{
    protected $baseUrl;

    /**
     * SetUp default for test selenium
     *
     * @return void
     */
    protected function setUp()
    {
        require_once "{$_SERVER['KERNEL_DIR']}/AppKernel.php";
        $kernel = new \AppKernel('test', true);
        $kernel->boot();

        /** @var \Symfony\Component\DependencyInjection\Container $container */
        $container = $kernel->getContainer();

        $this->baseUrl = $container->getParameter('base_url_selenium');

        $this->setBrowser('firefox');
        $this->setBrowserUrl($this->baseUrl);
    }
}
