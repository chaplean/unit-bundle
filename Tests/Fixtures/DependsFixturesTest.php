<?php

namespace Chaplean\Bundle\UnitBundle\Tests\Fixtures;

use Chaplean\Bundle\UnitBundle\Test\LogicalTest;

/**
 * DependsFixturesTest.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     2.0.0
 */
class DependsFixturesTest extends LogicalTest
{
    /**
     * @return void
     */
    public function testLoadFixturesWithoutDependencies()
    {
        self::loadStaticFixtures(array(
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
        self::loadStaticFixtures(array(
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
        self::loadStaticFixtures(array(
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
