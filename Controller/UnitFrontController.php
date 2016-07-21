<?php

namespace Chaplean\Bundle\UnitBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * UnitFrontController.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.com)
 * @since     2.2.0
 */
class UnitFrontController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        return new Response($request->getClientIp());
    }
}
