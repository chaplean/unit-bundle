<?php
namespace Tests\Chaplean\Bundle\UnitBundle\Test;

use Chaplean\Bundle\UnitBundle\Repository\ClientRepository;
use Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase;
use Chaplean\Bundle\UnitBundle\Test\LogicalTestCase;
use Doctrine\ORM\EntityManager;

/**
 * Class ClientRepositoryTest.
 *
 * @package   Tests\Chaplean\Bundle\UnitBundle\Test
 * @author    Tom - Chaplean <tom@chaplean.coop>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.coop)
 * @since     4.0.0
 */
class ClientRepositoryTest extends FunctionalTestCase
{
    /**
     * @return void
     */
    public function testRepositoryIsWorking()
    {
        $repository = $this->em->getRepository('ChapleanUnitBundle:Client');

        $this->assertInstanceOf(ClientRepository::class, $repository);
    }

    /**
     * @return void
     */
    public function testManagerIsAccessible()
    {
        $repository = $this->em->getRepository('ChapleanUnitBundle:Client');

        $this->assertInstanceOf(EntityManager::class, $repository->getManager());
    }
}
