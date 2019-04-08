<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\Translation\TranslatorInterface;
use Tests\Chaplean\Bundle\UnitBundle\Resources\Utility\ServiceLambda;

/**
 * Class ServiceDependentSharedServiceTest.
 *
 * @package   Tests\Chaplean\Bundle\UnitBundle\Utility
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2016 Chaplean (https://www.chaplean.coop)
 * @since     5.0.0
 */
class ServiceDependentSharedServiceTest extends FunctionalTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->translator = \Mockery::mock(TranslatorInterface::class);
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase::createClientWith
     *
     * @return void
     */
    public function testNotSharedMockBetweenClient(): void
    {
        $client = $this->createClientWith('');

        $client->getContainer()->set('translator', $this->translator);

        $this->assertSame($client->getContainer()->get(ServiceLambda::class)->getTranslator(), $this->translator);
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase::createClientWith
     *
     * @return void
     */
    public function testNotSharedMockBetweenClientSecondClient(): void
    {
        $client = $this->createClientWith('');

        $this->assertNotSame($client->getContainer()->get(ServiceLambda::class)->getTranslator(), $this->translator);
    }
}
