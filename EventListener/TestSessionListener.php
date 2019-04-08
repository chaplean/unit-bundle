<?php

namespace Chaplean\Bundle\UnitBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\EventListener\TestSessionListener as BaseTestSessionListener;

/**
 * Class TestSessionListener.
 *
 * @author    Matthias - Chaplean <matthias@chaplean.coop>
 * @copyright 2014 - 2017 Chaplean (https://www.chaplean.coop)
 * @since     5.2.5
 */
class TestSessionListener extends BaseTestSessionListener
{
    /**
     * @param GetResponseEvent $event
     *
     * @return void
     */
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
            if ($session->getId() !== $cookies->get($session->getName())) {
                $session->setId($cookies->get($session->getName()));
            }
        }
    }
}
