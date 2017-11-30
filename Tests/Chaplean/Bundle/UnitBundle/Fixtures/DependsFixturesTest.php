<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Fixtures;

use Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase;
use Chaplean\Bundle\UnitBundle\Test\LogicalTestCase;

/**
 * DependsFixturesTest.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.coop)
 * @since     2.0.0
 */
class DependsFixturesTest extends FunctionalTestCase
{
    /**
     * @return void
     */
    public static function setUpBeforeClass()
    {
        self::markTestSkipped('S');
        parent::setUpBeforeClass();
    }

    /**
     * @return void
     */
    public function testLoadFixturesWithoutDependencies()
    {
        $this->loadPartialFixtures(array(
            'Chaplean\Bundle\UnitBundle\DataFixtures\Liip\LoadClientData'
        ));

        $clients = $this->em->getRepository('Chaplean\Bundle\UnitBundle\Entity\Client')->findAll();
        $products = $this->em->getRepository('Chaplean\Bundle\UnitBundle\Entity\Product')->findAll();

        $this->assertCount(1, $clients);
        $this->assertCount(0, $products);
    }

    /**
     * @return void
     */
    public function testLoadFixturesWithDependencies()
    {
        $this->loadPartialFixtures(array(
            'Chaplean\Bundle\UnitBundle\DataFixtures\Liip\LoadProductData'
        ));

        $clients = $this->em->getRepository('Chaplean\Bundle\UnitBundle\Entity\Client')->findAll();
        $products = $this->em->getRepository('Chaplean\Bundle\UnitBundle\Entity\Product')->findAll();
        $providers = $this->em->getRepository('Chaplean\Bundle\UnitBundle\Entity\Provider')->findAll();

        $this->assertCount(1, $clients);
        $this->assertCount(1, $products);
        $this->assertCount(0, $providers);
    }

    /**
     * @return void
     */
    public function testLoadFixturesWithMultipleDependencies()
    {
        $this->loadPartialFixtures(array(
            'Chaplean\Bundle\UnitBundle\DataFixtures\Liip\LoadProviderData'
        ));

        $clients = $this->em->getRepository('Chaplean\Bundle\UnitBundle\Entity\Client')->findAll();
        $products = $this->em->getRepository('Chaplean\Bundle\UnitBundle\Entity\Product')->findAll();
        $providers = $this->em->getRepository('Chaplean\Bundle\UnitBundle\Entity\Provider')->findAll();

        $this->assertCount(1, $clients);
        $this->assertCount(1, $products);
        $this->assertCount(1, $providers);
    }
}
