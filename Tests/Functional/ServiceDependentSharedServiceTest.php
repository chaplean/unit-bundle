<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Functional;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Contracts\Translation\TranslatorInterface;
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
     * @var \Mockery\MockInterface|\Symfony\Component\Translation\TranslatorInterface
     */
    private static $translator;

    /**
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$translator = \Mockery::mock(TranslatorInterface::class);
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase::bootKernel
     *
     * @return void
     * @throws \Exception
     */
    public function testNotSharedMockBetweenKernel(): void
    {
        static::bootKernel();

        $this->getContainer()->set('translator', self::$translator);

        $this->assertSame($this->getContainer()->get(ServiceLambda::class)->getTranslator(), self::$translator);
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase::bootKernel
     *
     * @return void
     * @throws \Exception
     */
    public function testNotSharedMockBetweenKernelSecondKernel(): void
    {
        static::bootKernel();

        $this->assertNotSame($this->getContainer()->get(ServiceLambda::class)->getTranslator(), self::$translator);
        $this->assertNotSame($this->getContainer()->get('translator'), self::$translator);
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase::createClientWith
     *
     * @return void
     * @throws \Exception
     */
    public function testNotSharedMockBetweenClient(): void
    {
        $client = $this->createClientWith('');

        $client->getContainer()->set('translator', self::$translator);

        $this->assertSame($client->getContainer()->get(ServiceLambda::class)->getTranslator(), self::$translator);
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase::createClientWith
     *
     * @return void
     * @throws \Exception
     */
    public function testNotSharedMockBetweenClientSecondClient(): void
    {
        $client = $this->createClientWith('');

        $this->assertNotSame($client->getContainer()->get(ServiceLambda::class)->getTranslator(), self::$translator);
    }
}
