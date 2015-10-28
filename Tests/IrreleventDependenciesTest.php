<?php

namespace Chaplean\Bundle\UnitBundle\Tests;

use Chaplean\Bundle\UnitBundle\Entity\Client;
use Chaplean\Bundle\UnitBundle\Entity\Enum;
use Chaplean\Bundle\UnitBundle\Test\LogicalTest;

/**
 * IrreleventDependenciesTest.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     2.0.0
 */
class IrreleventDependenciesTest extends LogicalTest
{
    /**
     * @return void
     */
    public function testAutoGeneratedClientData()
    {
        $this->assertCount(0, $this->em->getRepository('ChapleanUnitBundle:Client')->findAll());

        self::loadPartialFixtures(array('Chaplean\Bundle\UnitBundle\DataFixtures\Liip\ForIrreleventDependencies\LoadProductData'));

        /** @var Client[] $clients */
        $clients = $this->em->getRepository('ChapleanUnitBundle:Client')->findAll();

        $this->assertCount(1, $clients);
        $this->assertNotEquals('Chaplean', $clients[0]->getName());
    }

    /**
     * @return void
     */
    public function testAutoGeneratedClientAndProductData()
    {
        $this->assertCount(0, $this->em->getRepository('ChapleanUnitBundle:Client')->findAll());
        $this->assertCount(0, $this->em->getRepository('ChapleanUnitBundle:Product')->findAll());
        $this->assertCount(0, $this->em->getRepository('ChapleanUnitBundle:Provider')->findAll());

        self::loadPartialFixtures(array('Chaplean\Bundle\UnitBundle\DataFixtures\Liip\ForIrreleventDependencies\LoadProviderData'));

        $this->assertCount(1, $this->em->getRepository('ChapleanUnitBundle:Client')->findAll());
        $this->assertCount(1, $this->em->getRepository('ChapleanUnitBundle:Product')->findAll());
        $this->assertCount(1, $this->em->getRepository('ChapleanUnitBundle:Provider')->findAll());
    }

    /**
     * @return void
     */
    public function testAutoGeneratedType()
    {
        $this->assertCount(0, $this->em->getRepository('ChapleanUnitBundle:Enum')->findAll());

        self::loadPartialFixtures(array('Chaplean\Bundle\UnitBundle\DataFixtures\Liip\ForIrreleventDependencies\LoadEnumData'));

        /** @var Enum[] $enums */
        $enums = $this->em->getRepository('ChapleanUnitBundle:Enum')->findAll();
        $this->assertCount(1, $enums);

        $enum = $enums[0];
        $this->assertTrue(in_array($enum->getEnumType(), array('enum1','enum2','enum3')));
    }
}
