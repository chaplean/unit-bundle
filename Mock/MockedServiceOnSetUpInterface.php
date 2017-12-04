<?php

namespace Chaplean\Bundle\UnitBundle\Mock;

/**
 * Class MockedServiceOnSetUp.
 *
 * @package   Chaplean\Bundle\UnitBundle\Mock
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2017 Chaplean (http://www.chaplean.coop)
 * @since     7.0.0
 */
interface MockedServiceOnSetUpInterface
{
    /**
     * Return an associative array
     *
     * example:
     * ```
     *      $knpPdf = \Mockery::mock('Knp\Bundle\SnappyBundle\Snappy\LoggableGenerator');
     *      $knpPdf->shouldReceive('getOutputFromHtml')->andReturn('example');
     *      $knpPdf->shouldReceive('getOutput')->andReturn('example');
     *
     *      return ['knp_snappy.pdf' => $knpPdf]
     * ```
     *
     * @return array
     */
    public static function getMockedServices();
}
