<?php

namespace Chaplean\Bundle\UnitBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Bundle\FrameworkBundle\EventListener\TestSessionListener as BaseTestSessionListener;

/**
 * Class TestSessionListener.
 *
 * @author    Matthias - Chaplean <matthias@chaplean.com>
 * @copyright 2014 - 2017 Chaplean (http://www.chaplean.com)
 * @since     5.2.5
 */
class TestSessionListener extends BaseTestSessionListener
{
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        // bootstrap the session
        $session = $this->getSession();
        if (!$session) {
            return;
        }

        $cookies = $event->getRequest()->cookies;

        if ($cookies->has($session->getName())) {
            if ($session->getId() != $cookies->get($session->getName())) {
                $session->setId($cookies->get($session->getName()));
            }
        }
    }
}