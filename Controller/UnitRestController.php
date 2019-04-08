<?php

namespace Chaplean\Bundle\UnitBundle\Controller;

use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * UnitRestController.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2016 Chaplean (https://www.chaplean.coop)
 * @since     2.2.0
 *
 * @Annotations\RouteResource("Unit")
 *
 * @codeCoverageIgnore
 */
class UnitRestController extends FOSRestController
{
    /**
     * @Annotations\Get("/unit/200")
     * @return Response
     */
    public function get200Action()
    {
        return $this->handleView($this->view([]));
    }

    /**
     * @Annotations\Get("/unit/404")
     * @return Response
     */
    public function get404Action()
    {
        return $this->handleView($this->view([], 404));
    }

    /**
     * @Annotations\Get("/unit/object")
     * @return Response
     */
    public function getObjectAction()
    {
        return $this->handleView($this->view(
            [
            'id' => 1,
            'name' => 'foo'
            ]
        ));
    }

    /**
     * @Annotations\Post("/unit")
     * @return Response
     */
    public function postAction()
    {
        return $this->handleView($this->view([]));
    }

    /**
     * @param Request $request
     *
     * @Annotations\Get("/unit")
     * @return Response
     */
    public function getWithRequestAction(Request $request)
    {
        return $this->handleView($this->view([$request->getUri()]));
    }

    /**
     * @param integer $id
     *
     * @Annotations\Get("/unit/{id}")
     * @return Response
     */
    public function getWithoutRequestAction($id)
    {
        return $this->handleView($this->view([$id]));
    }

    /**
     * @param Request $request
     * @param string  $id
     *
     * @Annotations\Get("/unit/request/{id}")
     * @return Response
     */
    public function getWithRequestAndParameterAction(Request $request, $id)
    {
        return $this->handleView($this->view([$request->getUri(), $id]));
    }

    /**
     * @param Request $request
     *
     * @Annotations\Get("/unit/query")
     * @return Response
     */
    public function getWithQueryAction(Request $request)
    {
        return $this->handleView($this->view([$request->query->get('limit')]));
    }

    /**
     * @param Request $request
     *
     * @Annotations\Post("/unit/request")
     * @return Response
     */
    public function postWithResquestAction(Request $request)
    {
        return $this->handleView($this->view([$request->request->get('name')]));
    }

    /**
     * @Annotations\Delete("/unit")
     * @return Response
     */
    public function deleteAction()
    {
        return $this->handleView($this->view([]));
    }

    /**
     * @Annotations\Put("/unit")
     * @return Response
     */
    public function putAction()
    {
        return $this->handleView($this->view([]));
    }
}
